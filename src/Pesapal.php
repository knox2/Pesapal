<?php

namespace Knox\Pesapal;

use Knox\Pesapal\Contracts\PesapalContract;
use Knox\Pesapal\Exceptions\PesapalException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Class Pesapal
 *
 * @package Knox\Pesapal
 */
class Pesapal implements PesapalContract
{

    /**
     * Get API path
     * @param null $path
     * @return string
     */
    public function api_link($path = null)
    {
        $live = 'https://pay.pesapal.com/v3/api/';
        $demo = 'https://cybqa.pesapal.com/pesapalv3/api/';
        return (config('pesapal.live') ? $live : $demo) . $path;
    }

    /**
     * Get API Token
     * @param null $path
     * @return string
     * @throws PesapalException
     */
    private function getToken(){
        $token_key = 'pesapal_v3__access_token';
        $token = Cache::get($token_key, null);

        if($token){
            return $token;
        }

        $consumer_key = config('pesapal.consumer_key');
        $consumer_secret = config('pesapal.consumer_secret');
        $url = $this->api_link('Auth/RequestToken');

       $response = Http::post($url, [
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
        ]);

         $response_data = $response->json();
         if($response_data['status'] == "200"){
             $token =  $response_data['token'];
             Cache::put($token_key, $token, now()->addMinutes(4));
             return $token;
         }

        $this->makeException("Invalid consumer secret/key", $response_data);

        return false;
    }

    /**
     * Register IPN
     * @param $ipn_url
     * @param $method
     * @return object | bool
     * @throws PesapalException
     */
    public function registerIPN($ipn_url, $method = 'GET'){
        $token = $this->getToken();

        $url = $this->api_link('URLSetup/RegisterIPN');

        $response = Http::withToken($token)->post($url, [
            'url' => $ipn_url,
            'ipn_notification_type' => $method,
        ]);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            return $response_data;
        }

        $this->makeException("Unable to register IPN", $response_data);

        return false;
    }

    /**
     * Get All Registered IPNs
     * @return array
     * @throws PesapalException
     */
    public function getIPNs(){
        $token = $this->getToken();

        $url = $this->api_link('URLSetup/GetIpnList');

        $response = Http::withToken($token)->get($url);

        $response_data = $response->json();

        if(array_key_exists('status', $response_data)){
            $this->makeException("Unable to get IPNs", $response_data);
        }

        return $response_data;
    }

    /**
     * Get Active Payment IPN
     * @return string
     * @throws PesapalException
     */
    private function getPaymentIPN(){
        $app_ipn = route('pesapal-ipn');
        $cached_ipn_key = 'pesapal_registered_ipn';
        $cached_ipn = Cache::get($cached_ipn_key, null);
        if(!is_null($cached_ipn)){
            if($cached_ipn[0] === $app_ipn){
                return $cached_ipn[1];
            } else{
                Cache::forget($cached_ipn_key);
            }
        }
        $ipns = $this->getIPNs();
        $main_ipn_id = "";
        foreach ($ipns as $ipn){
            if($ipn['url'] === $app_ipn){
                $main_ipn_id = $ipn['ipn_id'];
            }
        }

        if(!$main_ipn_id){
            $new_ipn = $this->registerIPN($app_ipn);
            $main_ipn_id = $new_ipn['ipn_id'];
        }

        Cache::forever($cached_ipn_key, [$app_ipn, $main_ipn_id]);
        return $main_ipn_id;
    }

    /**
     * Make payment with order details
     * @param $params
     * @return string
     * @throws PesapalException
     */
    public function makePayment($params)
    {

        if (!config('pesapal.callback_route')) {
            $this->makeException('callback route not provided', ['error' => ['message' => 'N/A']]);
        } else {
            if (!Route::has(config('pesapal.callback_route'))) {
                $this->makeException("callback route does not exist", ['error' => ['message' => 'N/A']]);
            }
        }

        if(isset($params['notification_id'])) {
            $notification_id = $params['notification_id'];
        } else{
            $notification_id = $this->getPaymentIPN();
        }

        $defaults = [
            "id" => $this->random_reference(),
            "currency" => "KES",
            "amount" => 1,
            "description" => "",
            "callback_url" => route(config('pesapal.callback_route')),
            "redirect_mode" => "TOP_WINDOW",
            "cancellation_url" => "",
            "notification_id" => $notification_id,
            "branch" => "",
            "billing_address" => [
                "email_address" => "",
                "phone_number" => "",
                "country_code" => "KE",
                "first_name" => "",
                "middle_name" => "",
                "last_name" => "",
                "line_1" => "",
                "line_2" => "",
                "city" => "",
                "state" => "",
                "postal_code" => "",
                "zip_code" => ""
            ],
//            "account_number" => "",
//            "subscription_details" => [
//                "start_date" => "24-01-2023",
//                "end_date" => "31-12-2023",
//                "frequency" => "DAILY"
//            ]
        ];

        $params = array_merge([
            'width' => '100%',
            'height' => '100%',
        ], $params);

        isset($params['currency']) && $defaults['currency'] = $params['currency'];

        if (!array_key_exists('currency', $params)) {
            if (null != config('pesapal.currency')) {
                $defaults['currency'] = config('pesapal.currency');
            }
        }

        isset($params['reference']) && $defaults['id'] = $params['reference'];
        isset($params['redirect_mode']) && $defaults['redirect_mode'] = $params['redirect_mode'];
        isset($params['cancellation_url']) && $defaults['cancellation_url'] = $params['cancellation_url'];
        isset($params['branch']) && $defaults['branch'] = $params['branch'];
        isset($params['amount']) && $defaults['amount'] = $params['amount'];
        isset($params['description']) && $defaults['description'] = $params['description'];
        isset($params['first_name']) && $defaults['billing_address']['first_name'] = $params['first_name'];
        isset($params['middle_name']) && $defaults['billing_address']['middle_name'] = $params['middle_name'];
        isset($params['last_name']) && $defaults['billing_address']['last_name'] = $params['last_name'];
        isset($params['email']) && $defaults['billing_address']['email_address'] = $params['email'];
        isset($params['phonenumber']) && $defaults['billing_address']['phone_number'] = $params['phonenumber'];
        isset($params['country_code']) && $defaults['billing_address']['country_code'] = $params['country_code'];
        isset($params['line_1']) && $defaults['billing_address']['line_1'] = $params['line_1'];
        isset($params['line_2']) && $defaults['billing_address']['line_2'] = $params['line_2'];
        isset($params['city']) && $defaults['billing_address']['city'] = $params['city'];
        isset($params['state']) && $defaults['billing_address']['state'] = $params['state'];
        isset($params['postal_code']) && $defaults['billing_address']['postal_code'] = $params['postal_code'];
        isset($params['zip_code']) && $defaults['billing_address']['zip_code'] = $params['zip_code'];

        $token = $this->getToken();

        $url = $this->api_link('Transactions/SubmitOrderRequest');

        $response = Http::withToken($token)->post($url, $defaults);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            return '<iframe src="' . $response_data['redirect_url'] . '" width="' . $params['width'] . '" height="' . $params['height'] . '" scrolling="auto" frameBorder="0"> <p>Unable to load the payment page</p> </iframe>';
        }

        $this->makeException("Unable to make payment", $response_data);

        return false;
    }

    /**
     * @param $pesapalNotification
     * @param $pesapal_merchant_reference
     * @param $pesapalTrackingId
     * @return bool|\Illuminate\Http\JsonResponse
     * @throws PesapalException
     */
    public function redirectToIPN($pesapalNotification, $pesapal_merchant_reference, $pesapalTrackingId)
    {

        $token = $this->getToken();

        $url = $this->api_link('Transactions/GetTransactionStatus');

        $response = Http::withToken($token)->get($url,[
            'orderTrackingId' => $pesapalTrackingId,
        ]);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            $statuses = ['INVALID','COMPLETED','FAILED','REVERSED'];
            //UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
            $transaction_id = $pesapalTrackingId;
            $payment_method = $response_data['payment_method'];
            $merchant_reference = $pesapal_merchant_reference;
            $status = $statuses[$response_data['status_code']];
            $separator = explode('@', config('pesapal.ipn'));
            $controller = $separator[0];
            $method = $separator[1];
            $class = '\App\Http\Controllers\\' . $controller;
            $payment = new $class();
            $payment->$method($transaction_id, $status, $payment_method, $merchant_reference, $pesapalNotification, $response_data);

            return response()->json([
                'message' => 'Received'
            ]);
        }

        $this->makeException("Invalid order tracking ID", $response_data);

        return false;
    }

    /**
     * Make a refund
     * @param $confirmation_code
     * @param $amount
     * @param $username
     * @param $remarks
     * @return bool|object
     * @throws PesapalException
     */
    public function makeRefund($confirmation_code, $amount,$username, $remarks){
        $token = $this->getToken();

        $url = $this->api_link('Transactions/RefundRequest');

        $response = Http::withToken($token)->post($url, [
            'confirmation_code' => $confirmation_code,
            'amount' => $amount,
            'username' => $username,
            'remarks' => $remarks,
        ]);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            return $response_data;
        }

        $this->makeException("Unable to make a refund", $response_data);

        return false;
    }

    /**
     * Cancel Order
     * @param $order_tracking_id
     * @return string
     * @throws PesapalException
     */
    public function cancelOrder($order_tracking_id){
        $token = $this->getToken();

        $url = $this->api_link('Transactions/CancelOrder');

        $response = Http::withToken($token)->post($url, [
            'order_tracking_id' => $order_tracking_id,
        ]);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            return $response_data;
        }

        $this->makeException("Unable to cancel order", $response_data);

        return false;
    }

    /**
     * Get Transaction Status
     * @param $pesapalTrackingId
     * @return mixed
     * @throws PesapalException
     */
    function getMerchantStatus($pesapalTrackingId)
    {

        $token = $this->getToken();

        $url = $this->api_link('Transactions/GetTransactionStatus');

        $response = Http::withToken($token)->get($url,[
            'orderTrackingId' => $pesapalTrackingId,
        ]);

        $response_data = $response->json();
        if($response_data['status'] == "200"){
            $statuses = ['INVALID','COMPLETED','FAILED','REVERSED'];
            return $statuses[$response_data['status_code']];
        }

        $this->makeException("Unable to get transaction status", $response_data);

        return false;
    }


    /**
     * Generates a random reference / transaction id
     * @param string $prefix
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function random_reference($prefix = 'PESAPAL', $length = 15)
    {
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $str = '';

        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }

        return $prefix . $str;
    }

    /**
     * Throws an exception
     * @param $message
     * @param $data
     * @return null
     * @throws PesapalException
     */
    private function makeException($message, $data){
        throw new PesapalException($message." : ".$data['error']['message']);
    }

}