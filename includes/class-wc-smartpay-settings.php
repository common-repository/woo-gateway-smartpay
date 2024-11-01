<?php
/**
 * Plugin Name: WooCommerce SmartPay Payment Gateway
 * Plugin URI: https://smartpay.curexe.com
 * Description: Allows your WooCommerce store to accept payments using your bank (e-Transfer/Debit)
 * @author  Swift Racks
 * Author URI: https://swiftracks.com/
 * @version 1.0.0
 *
 * @license GPLv2
 * Text Domain: wc-smartpay-gateway
 * @package /includes
 *
 */


/**
 * Holds Smartpay settings
 */

class WC_SmartPay_Settings
{


    private $apiKey;

    private $IframeKey;

    private $description;

    private $title;

    private $payment_page_name;


    public  function __construct($apiKey,$IframeKey,  $description, $title, $payment_page_name){
        $this->apiKey = $apiKey;
        $this->IframeKey = $IframeKey;
        $this->description =  $description;
        $this->title = $title;
        $this->payment_page_name = $payment_page_name;

    }


    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * Returns the Iframe key
     * @return the Iframe key
     */
    public function getIframeKey()
    {
        return $this->IframeKey;
    }

    /**
     * Sets the Iframe key
     * @param the new value of Iframe to set
     */
    public function setIframeKey($IframeKey)
    {
        $this->IframeKey = $IframeKey;
    }

    /**
     * Returns the description
     * @return the description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the new value of the description
     * @param $description the new value to set
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the value of the title
     * @return the value of the title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the new value of the title
     * @param $title the new value to set
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the payment page name
     * @return the payment page name
     */
    public function get_payment_page_name()
    {
        return $this->payment_page_name;
    }

    /**
     * Set the new value of the page name
     * @param $payment_page_name the new value to set
     */
    public function set_payment_page_name($payment_page_name)
    {
        $this->payment_page_name = $payment_page_name;
    }



}