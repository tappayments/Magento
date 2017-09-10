<?php

namespace Gateway\Tap\Controller\Standard;

class Cancel extends \Gateway\Tap\Controller\Tap
{

    public function execute()
    {
        $this->_cancelPayment();
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->getTapHelper()->getUrl('checkout')
        );
    }

}
