<?php
/**
 * Plugin Name: WooCommerce SmartPay Payment Gateway
 * Plugin URI: https://smartpay.curexe.com
 * Description: Allows your WooCommerce store to accept payments using your bank (e-Transfer/Debit)
 * @author  Swift Racks
 * Author URI: https://swiftracks.com/
 * @version  2.0.0
 * @license GPLv2
 * Text Domain: wc-smartpay-gateway
 * @package /inclues
 *
 */



define( 'WC_SMARTPAY_CURR_VER', 'V2-0' );


require( dirname(__FILE__) . '/' . WC_SMARTPAY_CURR_VER . '/smart_pay_properties.php' );

include_once( 'class-wc-smartpay-helper.php' );

/**
 * A templates that performs a REST API calls to Smartpay
 */
class class_wc_rest_template
{
    /**
     * @var string The authentication  URL to authenticate on Smartpay
     */
    private static $authn_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_AUTHN_URL;

    /**
     * @var string The Smartpay order URL
     */
    private static $order_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_ORDER_URL;

    /**
     * @var string The SmartPay invoice URL
     */
    private static $invoice_url =  WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_INVOICES_URL;

    private static $get_webhooks_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_GET_WEBHOOKS_URL;

    private static $create_webhooks_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_CREATE_WEBHOOKS_URL;

    private static $modify_webhooks_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_MODIFY_WEBHOOKS_URL;

    private static $delete_webhooks_url = WC_SMARTPAY_API_URL . WC_SMARTPAY_API_VER . WC_SMARTPAY_DELETE_WEBHOOKS_URL;

    private static $refunds_path_url =   WC_SMARTPAY_REFUNDS_WEBHOOKS_URL;


    /**
     * @var The $curl command
     */
    private $curl;

    /**
     * @var The Smartpay authentication token
     */
    private $smartpay_auth_token;

    /**
     * @var WC_SmartPay_Settings Smartpay setting
     */
    public $WC_SmartPay_Settings;

    public function __construct($WC_SmartPay_Settings)
    {

        $this->init();

        $this->smartpay_settings = $WC_SmartPay_Settings;

        // Retrieve authentication token from Smartpay

        $this->smartpay_auth_token = $this->build_smartpay_authn();

    }

    /**
     * An initialization function
     */
    private function init() {

        // init curl
        $this->curl = curl_init();



    }

    /**
     * Creates, builds Smartpay authentication and attempts to authenticate given the API key
     * @return Returns the authentication token
     */
    private function build_smartpay_authn (){


        $auth_header = base64_encode($this->smartpay_settings->getIframeKey(). ":" . $this->smartpay_settings->getApiKey());

        $headers = array();

        $headers[] = 'Authorization: ' . 'Basic ' . $auth_header;

        $authn = $this->make_request("GET", self::$authn_url, $headers);

        $authn_token = $authn['result']['access_token'];

        return $authn_token;

    }

    public function check_smartpay_webhook_exist(){

        $requestData = array(
            'topic'=> 'invoice/created',
            'url'=> WC_SmartPay_Helper::get_webhook_url()
        );

        $check_webhook_exist =  $this->make_request("GET", self::$get_webhooks_url, $this->init_auth_bearer_header() , $requestData )['number_of_results'];
        if($check_webhook_exist == 0){
            $this->register_webhooks();
        }

    }

    public function register_webhooks(){

        //$access_token = $this->build_smartpay_authn();

        $invoice_create_post_data = array(
            //'token' => $access_token,
            'topic'=> 'invoice/created',
            'url'=> WC_SmartPay_Helper::get_webhook_url(),
        );

        $create_invoice_webhook =  $this->make_request("POST", self::$create_webhooks_url, $this->init_auth_bearer_header() , $invoice_create_post_data )['result']['webhook_id'];

        $invoice_paid_post_data = array(
            //'token' => $access_token,
            'topic'=> 'invoice/paid',
            'url'=> WC_SmartPay_Helper::get_webhook_url(),
        );


        $paid_invoice_webhook =  $this->make_request("POST", self::$create_webhooks_url, $this->init_auth_bearer_header() , $invoice_paid_post_data )['result']['webhook_id'];

        $order_created_post_data = array(
            //'token' => $access_token,
            'topic'=> 'order/created',
            'url'=> WC_SmartPay_Helper::get_webhook_url(),
        );

        $order_created_webhook =  $this->make_request("POST", self::$create_webhooks_url, $this->init_auth_bearer_header() , $order_created_post_data )['result']['webhook_id'];

        $invoice_refunded_post_data = array(
            //'token' => $access_token,
            'topic'=> 'invoice/refunded',
            'url'=> WC_SmartPay_Helper::get_webhook_url(),
        );

        $invoice_refunded_webhook =  $this->make_request("POST", self::$create_webhooks_url, $this->init_auth_bearer_header() , $invoice_refunded_post_data  )['result']['webhook_id'];

    }

    /**
     * Makes a request given the URL, method, and request data
     * @param $method the HTTP method
     * @param $url the URL to make request to
     * @param $requestData the request data
     * @param $headers the headers to includes
     * @param bool $as_json_response a boolean value to indicates whether to return a Json response.
     * Default is true
     * @return The JSON response of the result request
     */
    private function make_request($method , $url,  $headers, $requestData = [], $as_json_response = true) {


        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => http_build_query($requestData)
        ));

        $response = curl_exec($this->curl);

       return $as_json_response ?  json_decode($response, true) : $response;


    }

    /**
     * Returns a list of orders who their give status
     * @param $status the status to query for the orders
     * @return stdClass|WC_Order[] of orders who their status is on-hold
     */
    private function get_wc_orders_by_status($status) {
        return $wc_orders = wc_get_orders( array(
            'status' => $status
        ) );

    }

    /**
     * Returns the the query result information of an order Smartpay for the given order
     * @param $wc_order the order to query Smartpay
     * @return the the query result from Smartpay if it exists. Otherwise, null
     */
    public function get_smartpay_order($wc_order){

        $requestData = array(
            'format' => 'json',
            'custom_order_id'=> $wc_order->get_id()
        );

        $order_result =  $this->make_request("GET", self::$order_url,  $this->init_auth_bearer_header(), $requestData )['result'];


        // return the first result that is enabled and not dummy
        if(!empty($order_result)){
            foreach($order_result as $order){
                if(($order['is_enabled'] === true || $order['is_enabled'] == 'true') and ($order['is_dummy'] === false || $order['is_dummy'] == 'false')){
                    return $order;
                }
            }
        }

    }

    /**
     * Returns the Smartpay invoice for the given Smartpay Order
     * @param $smartpay_order the Smartpay order to query for an invoice
     * @return  the Smartpay invoice for the given Smartpay Order |null
     */
    public function get_smartpay_invoice($smartpay_order){

        $requestData = array(
            'format' => 'json',
            'order_id'=> $smartpay_order['order_id'],
        );


        $invoice_result = $this->make_request("GET", self::$invoice_url,  $this->init_auth_bearer_header()  , $requestData)['result'];

        if(!empty($invoice_result)){
            foreach($invoice_result as $invoice){
                if(($invoice['is_enabled'] === true || $invoice['is_enabled'] == 'true') and ($invoice['is_dummy'] === false || $invoice['is_dummy'] == 'false')){
                    return $invoice;
                }
            }
        }

    }

    /**
     * Updates the woocommerce orders
     * @throws WC_Data_Exception if an exception has occured
     */
    public function update_wc_order_status(){

        $all_on_hold_pending_orders = array_merge($this->get_wc_orders_by_status('pending'), $this->get_wc_orders_by_status('on-hold'));


        foreach ($all_on_hold_pending_orders as $order){

            $smartpay_order = $this->get_smartpay_order($order);

            if($smartpay_order != null){

                $smartpay_invoice = $this->get_smartpay_invoice($smartpay_order);

                if($smartpay_invoice != null){

                    $payment_method = $smartpay_invoice['payment_method'];
                    $smartpay_invoice_id = $smartpay_invoice['invoice_id'];
                    if(!empty($smartpay_invoice['date_paid'])){

                        if($payment_method == 'e'){
                            $payment_method = 'Interac E-transfer';
                        }else{
                            $payment_method = 'Debit Card';
                        }

                        $order->update_status('processing', 'Payment has been completed using payment method: ' . $payment_method . ' SmartPay Charge ID: <b>' . $smartpay_invoice_id . '</b>');


                    }  else {
                        $order->update_status('on-hold', 'Payment is on hold using payment method: ' . $payment_method . ' SmartPay Charge ID: <b>' . $smartpay_invoice_id . '</b>');

                    }
                    $order->update_meta_data('_smartpay_invoice_id', $smartpay_invoice_id);
                    $order->update_meta_data('_smartpay_order_id', $smartpay_order['order_id']);
                    $order->update_meta_data('_smartpay_payment_method', $payment_method);

                    $order->set_payment_method($payment_method);

                }
            }

        }
        curl_close($this->curl);


    }


    public function get_wc_orders_by_order_id($id) {
        return wc_get_order($id);
    }

    /**
     * Prepares the Authorization Header to be added to array of headers
     *
     * @return array the array of headers which contains Authorization header
     */
    private function init_auth_bearer_header()
    {
        $headers = array();

        $headers[] = 'Authorization: ' . 'Bearer ' . $this->smartpay_auth_token;

        return $headers;
    }


    private function notify_smartpay_refund($smartpay_invoice_id) {

        return $this->make_request("PUT", self::$invoice_url .   "/" . $smartpay_invoice_id .  self::$refunds_path_url ,  $this->init_auth_bearer_header() );

    }


    public function process_wc_order_refund($wc_order) {
        //check if the order status if its processing or completed
        if($wc_order->get_status() == 'processing' || $wc_order->get_status() == 'completed') {
            $smartpay_order = $this->get_smartpay_order($wc_order);
            if($smartpay_order != null) {
                $smartpay_invoice = $this->get_smartpay_invoice($smartpay_order);
                  $response = $this->notify_smartpay_refund($smartpay_invoice['invoice_id']);
                   $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
                   if($httpCode == 200) {
                       $wc_order->add_order_note('Order refund process has been initiated for'
                                   . ' SmartPay  invoice  ID: <b>' . $smartpay_invoice['invoice_id']
                                . '. Please await confirmation from Smartpay. Note this process may take a few days.');
                   } else {
                       $wc_order->add_order_note('Order refund process has been declined for'
                           . ' SmartPay  invoice  ID: <b>' . $smartpay_invoice['invoice_id']
                           . '. Reason:'. $response['errors']['400'] . ' Contact Smartpay to resolve the issue');

                   }


            }
            curl_close($this->curl);

        }




    }

    /**
     * De-registers webhooks from Smartpay
     */
    public function de_register_webhooks() {
        $webhooks =  $this->make_request("GET", self::$get_webhooks_url, $this->init_auth_bearer_header())['result'];
        foreach($webhooks as $webhook) { //foreach element in $arr
            $webhook_id = $webhook['webhook_id']; //etc
            $this->make_request("DELETE", self::$get_webhooks_url . '/' .$webhook_id, $this->init_auth_bearer_header());

        }

        curl_close($this->curl);

    }

}