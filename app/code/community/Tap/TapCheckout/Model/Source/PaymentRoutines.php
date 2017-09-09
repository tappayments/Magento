<?php


class Tap_TapCheckout_Model_Source_PaymentRoutines
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'multi', 'label' => 'Multi-page Payment Routine'),
            array('value' => 'single', 'label' => 'Single Page Payment Routine'),
        );
    }
}