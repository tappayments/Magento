<?php

namespace Gateway\Tap\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $session;
    public $TAP_PAYMENT_URL_PROD = "https://www.gotapnow.com/webpay.aspx";

    public $TAP_PAYMENT_URL_TEST = "http://live.gotapnow.com/webpay.aspx";
 
    public function __construct(Context $context, \Magento\Checkout\Model\Session $session) {
        $this->session = $session;
        parent::__construct($context);
    }

    public function cancelCurrentOrder($comment) {
        $order = $this->session->getLastRealOrder();
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }
	
	public function validateResponse($returnParams) 
	{
		$orderId	=	$_REQUEST['trackid'];
        $key		=	$this->getConfigData("MID");
		$salt		=	$this->getConfigData("merchant_key");
		$RefID=$_REQUEST['ref'];
		$str = 'x_account_id'.$key.'x_ref'.$RefID.'x_resultSUCCESSx_referenceid'.$orderId.'';
		$HashString = hash_hmac('sha256', $str, $salt);
		$responseHashString=$_REQUEST['hash'];
		if($HashString==$responseHashString)
		{
			return true;
		}
		else
		{
			return false;
		}
    }

	
    public function restoreQuote() {
        return $this->session->restoreQuote();
    }

    public function getUrl($route, $params = []) {
        return $this->_getUrl($route, $params);
    }
}
