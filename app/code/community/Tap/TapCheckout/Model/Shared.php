<?php  

class Tap_TapCheckout_Model_Shared extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'tapcheckout_shared';

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_formBlockType = 'tapcheckout/shared_form';
    protected $_paymentMethod = 'shared';
     
    
    protected $_order;


    public function cleanString($string) {
        
        $string_step1 = strip_tags($string);
        $string_step2 = nl2br($string_step1);
        $string_step3 = str_replace("<br />","<br>",$string_step2);
        $cleaned_string = str_replace("\""," inch",$string_step3);        
        return $cleaned_string;
    }


    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
    
    
    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')
                            ->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId());
        }
        return $this->_order;
    }
	

    public function getCustomerId()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/customer_id');
    }
	
    public function getAccepteCurrency()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/currency');
    }
	
	
	
	
    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('tapcheckout/shared/redirect');
    }

    /**
     * prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getFormFields()
    {
	
	    $billing = $this->getOrder()->getBillingAddress();
        $coFields = array();
        $items = $this->getQuote()->getAllItems();
		
		/*if ($items) {
            $i = 1;
            foreach($items as $item){
                if ($item->getParentItem()) {
                   continue;
                }        
                $coFields['c_prod_'.$i]            = $this->cleanString($item->getSku());
                $coFields['c_name_'.$i]            = $this->cleanString($item->getName());
                $coFields['c_description_'.$i]     = $this->cleanString($item->getDescription());
                $coFields['c_price_'.$i]           = number_format($item->getPrice(), 2, '.', '');
            $i++;
            }
        }*/
        
        $request = '';
        /*foreach ($coFields as $k=>$v) {
            $request .= '<' . $k . '>' . $v . '</' . $k . '>';
        }*/
		
		
		$key=Mage::getStoreConfig('payment/tapcheckout_shared/key');
		$username=Mage::getStoreConfig('payment/tapcheckout_shared/username');
		$password=Mage::getStoreConfig('payment/tapcheckout_shared/password');
		$salt=Mage::getStoreConfig('payment/tapcheckout_shared/salt');
		$debug_mode=Mage::getStoreConfig('payment/tapcheckout_shared/debug_mode');
	
	    $orderId = $this->getOrder()->getRealOrderId(); 
	    $txnid = $orderId; 
		
		$CurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$coFields['MEID']          = $key;
		$coFields['UName']          = $username;
		$coFields['PWD']          = $password;
		$coFields['OrdID']        =  $txnid;
		$coFields['CurrencyCode'] = $CurrencyCode;
		
		$coFields['ItemName1']  = 'Order ID : '.$orderId;  
		$coFields['ItemQty1']  = '1';   
		$coFields['ItemPrice1']       =  $this->getOrder()->getBaseGrandTotal(); 
		$total = $this->getOrder()->getBaseGrandTotal(); 
		
		$coFields['productinfo']  = 'product Information';  
		$coFields['CstFName']    = $billing->getFirstname();
		$coFields['CstLName']     = $billing->getLastname();
		$name = $billing->getFirstname().' '.$billing->getLastname();
		
		//$coFields['City']         = $billing->getCity();
        //$coFields['State']        = $billing->getRegion();
		//$coFields['Country']      = $billing->getCountry();
        //$coFields['Zipcode']      = $billing->getPostcode();
		$coFields['CstEmail']        = $this->getOrder()->getCustomerEmail();
        $coFields['CstMobile']        = $billing->getTelephone();
		$mobile = $billing->getTelephone();
		
		$coFields['ReturnURL']         =  Mage::getBaseUrl().'tapcheckout/shared/success/';  
		$coFields['FailURL']         =  Mage::getBaseUrl().'tapcheckout/shared/failure/';
		$coFields['CancelURL']         =  Mage::getBaseUrl().'tapcheckout/shared/canceled/id/'.$this->getOrder()->getRealOrderId();
		
		

		
		$coFields['Pg']           =  'CC';
		$debugId='';
		
		$str = 'X_MerchantID'.$key.'X_UserName'.$username.'X_ReferenceID'.$txnid.'X_CurrencyCode'.$CurrencyCode.'X_Total'.$total.'';
		$hashstr = hash_hmac('sha256', $str, $salt);
		
        if ($debug_mode==1) {
		
		$requestInfo= $hashstr;
					$debug = Mage::getModel('tapcheckout/api_debug')
						->setRequestBody($requestInfo)
						->save();
							
					$debugId = $debug->getId();	
					
					$coFields['udf1']=$debugId;
					$coFields['Hash']    =  $hashstr;
				}
		else
		{
		 $coFields['Hash']         =  $hashstr;
		}
        return $coFields;
    }

    /**
     * Get url of Tap payment
     *
     * @return string
     */
    public function getTapCheckoutSharedUrl()
    {
        $mode=Mage::getStoreConfig('payment/tapcheckout_shared/demo_mode');
		
		$url='http://live.gotapnow.com/webpay.aspx';
		
		if($mode=='')
		{
		  $url='https://www.gotapnow.com/webpay.aspx';
		}
		 
         return $url;
    }
       

    /**
     * Get debug flag
     *
     * @return string
     */
    public function getDebug()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/debug_flag');
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        $payment->setStatus(self::STATUS_DECLINED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     * parse response POST array from gateway page and return payment status
     *
     * @return bool
     */
    public function parseResponse()
    {       

            return true;
    
    }

    /**
     * Return redirect block type
     *
     * @return string
     */
    public function getRedirectBlockType()
    {
        return $this->_redirectBlockType;
    }

    /**
     * Return payment method type string
     *
     * @return string
     */
    public function getPaymentMethodType()
    {
        return $this->_paymentMethod;
    }
	
	
	public function getResponseOperation($response)
	{
	   
	   $order = Mage::getModel('sales/order');	
	   $debug_mode=Mage::getStoreConfig('payment/tapcheckout_shared/debug_mode');
	   $key=Mage::getStoreConfig('payment/tapcheckout_shared/key');
	   $salt=Mage::getStoreConfig('payment/tapcheckout_shared/salt');
	    if(isset($_REQUEST['result']))
		{
		   $txnid=$_REQUEST['trackid'];

		   $orderid=$txnid;
		   if($_REQUEST['result']=='SUCCESS')
			{		   
				$RefID=$_REQUEST['ref'];
				$status='success';
				$order->loadByIncrementId($orderid);
				
				$str = 'x_account_id'.$key.'x_ref'.$RefID.'x_resultSUCCESSx_referenceid'.$txnid.'';
				$HashString = hash_hmac('sha256', $str, $salt);
				$responseHashString=$_REQUEST['hash'];
				if($HashString==$responseHashString)
				{
						$comment = 'Tap payment successful.<br/>Tap ID: '.$_REQUEST['ref'].' ('.$_REQUEST['trackid'].')<br/>Payment Type: '.$_REQUEST['crdtype'].'<br/>Payment Ref: '.$_REQUEST['payid'];
						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $comment);
						$order->sendNewOrderEmail();
						$order->setEmailSent(true);
						$order->save();
				}
				else
				{
					$order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
					$order->cancel()->save();
				}
			}
		   
		   if($_REQUEST['result']=='FAILURE')
		   {
		       $order->loadByIncrementId($orderid);
		       $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
		       // Invento updated 
			   $this->updateInventory($orderid);
			   
			   $order->cancel()->save();	   
		   }
		   else  if($_REQUEST['result']=='PENDING')
		   {
		       $order->loadByIncrementId($orderid);
		       $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
		       // Invento updated  
		       $this->updateInventory($orderid);
			   $order->cancel()->save();
		   }
		   
		}
        else
		{
		  		   
		   $order->loadByIncrementId($_REQUEST['trackid']);
		   $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
		  // Invento updated 
		   $order_id=$response['id'];
		   $this->updateInventory($order_id);
		   
		   $order->cancel()->save();
		}
	}
	
    public function updateInventory($order_id)
    {
  
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $items = $order->getAllItems();
		foreach ($items as $itemId => $item)
		{
		   $ordered_quantity = $item->getQtyToInvoice();
		   $sku=$item->getSku();
		   $product = Mage::getModel('catalog/product')->load($item->getProductId());
		   $qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
		  
		   $updated_inventory=$qtyStock+ $ordered_quantity;
					
		   $stockData = $product->getStockItem();
		   $stockData->setData('qty',$updated_inventory);
		   $stockData->save(); 
			
	   } 
    }
	
}