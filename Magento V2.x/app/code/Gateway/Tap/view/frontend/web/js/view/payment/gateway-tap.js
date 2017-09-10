define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tap',
                component: 'Gateway_Tap/js/view/payment/method-renderer/gateway-tap'
            }
        );
        return Component.extend({});
    }
 );