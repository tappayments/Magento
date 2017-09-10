<?php


class Tap_TapCheckout_Model_Source_DirectReturn
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'redirect', 'label' => 'Immediately returned to my website '),
            array('value' => 'link', 'label' => 'Given links only back to my website '),
        );
    }
}