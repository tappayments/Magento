<?php

class Tap_TapCheckout_Block_Shared_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $shared = $this->getOrder()->getPayment()->getMethodInstance();

        $form = new Varien_Data_Form();
        $form->setAction($shared->getTapCheckoutSharedUrl())
            ->setId('tapcheckout_shared_checkout')
            ->setName('tapcheckout_shared_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($shared->getFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }

        $html = '<html><body>';
        $html.= $this->__('</ br></ br></ br><p style="text-align:center">You will be redirected to Tap in a few seconds.</p>');
		$html.= $this->__('</ br><p style="text-align:center"><img src="https://www.gotapnow.com/web/tap.png"/></p>');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("tapcheckout_shared_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}