<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class WC_SmartPay_Helper {

    /**
     * Gets the webhook URL for Smartpay triggers. Used mainly for
     * asyncronous redirect payment methods in which statuses are
     * not immediately chargeable.
     *
     * @since 2.0.0
     * @version 2.0.0
     * @return string
     */
    public static function get_webhook_url() {
        return add_query_arg( 'wc-api', 'wc_smartpay', trailingslashit( get_home_url() ) );
    }

}