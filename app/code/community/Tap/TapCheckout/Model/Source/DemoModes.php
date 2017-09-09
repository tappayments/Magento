<?php


class Tap_TapCheckout_Model_Source_DemoModes
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'Y', 'label' => 'Demo Mode'),
            array('value' => '', 'label' => 'Production Mode'),
        );
    }
}