<?php

class Tap_TapCheckout_Block_Shared_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('tapcheckout/shared/form.phtml');
		$this->setMethodTitle('');
        $this->setMethodLabelAfterHtml('<img src="https://www.gotapnow.com/web/tap.png" height="20" alt="Tap"/>');
        parent::_construct();
    }
}