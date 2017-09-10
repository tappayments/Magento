<?php 

class Tap_TapCheckout_SharedController extends Tap_TapCheckout_Controller_Abstract
{
   
    protected $_redirectBlockType = 'tapcheckout/shared_redirect';
    protected $_paymentInst = NULL;
	
	
	public function  successAction()
    {
        $response = $this->getRequest()->getPost();
		Mage::getModel('tapcheckout/shared')->getResponseOperation($response);
        $this->_redirect('checkout/onepage/success');
    }
	
	
	
	 public function failureAction()
    {
       
	   $arrParams = $this->getRequest()->getPost();
	   Mage::getModel('tapcheckout/shared')->getResponseOperation($arrParams);
       $this->getCheckout()->clear();
	   $this->_redirect('checkout/onepage/failure');
    }


    public function canceledAction()
    {
	    $arrParams = $this->getRequest()->getParams();
	
       
		Mage::getModel('tapcheckout/shared')->getResponseOperation($arrParams);
		
		$this->getCheckout()->clear();
		$this->loadLayout();
        $this->renderLayout();
    }


   

    
}
    
    