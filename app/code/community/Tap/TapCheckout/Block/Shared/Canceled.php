<?php


class Tap_TapCheckout_Block_Shared_Canceled extends Mage_Core_Block_Template
{
    /**
     *  Return Error message
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $msg = Mage::getSingleton('checkout/session')->getTapCheckoutErrorMessage();
        Mage::getSingleton('checkout/session')->unsTapCheckoutErrorMessage();
        return $msg;
    }

    /**
     * Get continue shopping url
     */
    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}