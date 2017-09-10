<?php

namespace Gateway\Tap\Model;

use Gateway\Tap\Helper\Data as DataHelper;
use Gateway\Tap\Controller\Standard;

class Tap extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'tap';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_isOffline = true;
    protected $helper;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('INR');
    protected $_formBlockType = 'Gateway\Tap\Block\Form\Tap';
    protected $_infoBlockType = 'Gateway\Tap\Block\Info\Tap';
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Gateway\Tap\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_minAmount = "0.100";
        $this->_maxAmount = "1000000";
        $this->urlBuilder = $urlBuilder;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        /*f (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }*/
        return true;
    }

	public function buildTapRequest($order)
    {
		$billing_address = $order->getBillingAddress();
        $params = array('MEID' => $this->getConfigData("MID"),  
						'UName' => $this->getConfigData("merchant_username"),  				
                        'CurrencyCode' => $order->getOrderCurrencyCode(),
						'ItemPrice1' => round($order->getGrandTotal(), 3),
                        'CstFName' => $billing_address->getFirstName(),
						'CstLName' => $billing_address->getLastname(),
                        'CstEmail' => $order->getCustomerEmail(),
						'CstMobile' => $billing_address->getTelephone(),
                        'ItemName1' => 'Order '.$order->getRealOrderId(),
						'ItemQty1' => 1, 								
						'OrdID' => $order->getRealOrderId(), 						
                        'ReturnURL' => $this->urlBuilder->getUrl('tap/Standard/Response', ['_secure' => true]));    
        
		$str = 'X_MerchantID'.$params['MEID'].'X_UserName'.$params['UName'].'X_ReferenceID'.$params['OrdID'].'X_CurrencyCode'.$params['CurrencyCode'].'X_Total'.$params['ItemPrice1'].'';
		$hashstr = hash_hmac('sha256', $str, $this->getConfigData("merchant_key"));
        
        $params['Hash'] = $hashstr;        
		
        if($this->getConfigData('debug')){
            $url = $this->helper->TAP_PAYMENT_URL_TEST;
        }else{
            $url = $this->helper->TAP_PAYMENT_URL_PROD;
        }
        $tap_args_array = array();
		foreach($params as $key => $value){
			$tap_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
		}
        return '<form action="'.$url.'" method="post" name="tap" id="tap">
  				'. implode('', $tap_args_array) . '
				<p style="text-align:center"><br /><br /><a style="text-decoration: none;text-align:center" href="javascript:void(0);" onclick="encodeTxnRequest()">
				<span>You will be redirected to Tap. please click here if you are not redirected within 30 seconds</span>
				</a></p>
				<script type="text/javascript">
					document.tap.submit();
					function encodeTxnRequest()
					{
						document.tap.submit();
					}
				</script>
				</form>';
    }

    public function buildTapRequestold($order)
    {
		$billing_address = $order->getBillingAddress();
        $params = array('MEID' => $this->getConfigData("MID"),  
						'UName' => $this->getConfigData("merchant_username"),  				
                        'CurrencyCode' => $order->getOrderCurrencyCode(),
						'ItemPrice1' => round($order->getGrandTotal(), 3),
                        'CstFName' => $billing_address->getFirstName(),
						'CstLName' => $billing_address->getLastname(),
                        'CstEmail' => $order->getCustomerEmail(),
						'CstMobile' => $billing_address->getTelephone(),
                        'ItemName1' => 'Order '.$order->getRealOrderId(),
						'ItemQty1' => 1, 								
						'OrdID' => $order->getRealOrderId(), 						
                        'ReturnURL' => $this->urlBuilder->getUrl('tap/Standard/Response', ['_secure' => true]));    
        
		$str = 'X_MerchantID'.$params['MEID'].'X_UserName'.$params['UName'].'X_ReferenceID'.$params['OrdID'].'X_CurrencyCode'.$params['CurrencyCode'].'X_Total'.$params['ItemPrice1'].'';
		$hashstr = hash_hmac('sha256', $str, $this->getConfigData("merchant_key"));
        
        $params['Hash'] = $hashstr;        
		
        if($this->getConfigData('debug')){
            $url = $this->helper->TAP_PAYMENT_URL_TEST."?";
        }else{
            $url = $this->helper->TAP_PAYMENT_URL_PROD."?";
        }
        $urlparam = "";
		foreach($params as $key => $val){
			$urlparam = $urlparam.$key."=".$val."&";
		}
        $url = $url . $urlparam;
        return $url;
    }

    public function getRedirectUrl()
    {
        if($this->getConfigData('debug')){
            $url = $this->helper->TAP_PAYMENT_URL_TEST;
        }else{
            $url = $this->helper->TAP_PAYMENT_URL_PROD;
        }
        return $url;
    }

    public function getReturnUrl()
    {
        
    }

    public function getCancelUrl()
    {
        
    }
	
	public function validateResponse($returnParams) 
	{
		$orderId	=	$_REQUEST['trackid'];
        $key		=	$this->getConfigData("MID");
		$salt		=	$this->getConfigData("merchant_key");
		$RefID		=	$_REQUEST['ref'];
		$str 		= 	'x_account_id'.$key.'x_ref'.$RefID.'x_resultSUCCESSx_referenceid'.$orderId.'';
		$HashString = 	hash_hmac('sha256', $str, $salt);
		$responseHashString	=	$_REQUEST['hash'];
		if($HashString == $responseHashString)
		{
			return true;
		}
		else
		{
			return false;
		}
    }
}
