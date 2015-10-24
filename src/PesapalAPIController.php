<?php

namespace Knox\Pesapal;

use Illuminate\Support\Facades\Input as Input;
use App\Http\Controllers\Controller;
use Pesapal;
use Session;

class PesapalAPIController extends Controller
{

    function handleCallback(){
        $merchant_reference = Input::get('pesapal_merchant_reference');
        $tracking_id = Input::get('pesapal_transaction_tracking_id');
        $controller = Session::get('pesapal_haspaid_controller');
        $route = Session::get('pesapal_callback_route');
        return redirect($route.'/'.$tracking_id.'/'.$merchant_reference);
    }

    function handleIPN(){
        $notification_type = Input::get('pesapal_notification_type');
        $merchant_reference = Input::get('pesapal_merchant_reference');
        $tracking_id = Input::get('pesapal_transaction_tracking_id');
        $pesapal = new Pesapal;
        $pesapal -> redirectToIPN($notification_type,$merchant_reference,$tracking_id);
    }

    function test(){
        $details = array( // the defaults will be overidden if set in $params
            'amount' => '10',
            'description' => 'Test',
            'type' => 'MERCHANT',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@google.com',
            'phonenumber' => '254723232323',
            'live' => false,
            //'currency' => 'USD',
            'callback_route' => 'donepayment'
        );

        Pesapal::makePayment($details);
    }

}