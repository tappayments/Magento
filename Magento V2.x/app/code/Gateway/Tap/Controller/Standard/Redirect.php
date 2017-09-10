<?php

namespace Gateway\Tap\Controller\Standard;

class Redirect extends \Gateway\Tap\Controller\Tap
{
    public function execute()
    {
        $order = $this->getOrder();
        if ($order->getBillingAddress())
        {
			$this->addOrderHistory($order,'<br/>The customer was redirected to Tap');
			echo $this->getTapModel()->buildTapRequest($order);
            /*$this->getResponse()->setRedirect(
                $this->getTapModel()->buildTapRequest($order)
            );*/
        }
        else
        {
            $this->_cancelPayment();
            $this->_tapSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getTapHelper()->getUrl('checkout')
            );
        }
    }
}