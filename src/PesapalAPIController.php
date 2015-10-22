<?php

namespace Knox\Pesapal;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Knox\Pesapal\Pesapal;

class PesapalAPIController extends Controller
{

    function handleCallback(Request $request){
        $merchant_reference = Request::input('pesapal_merchant_reference');
        $tracking_id = Request::input('pesapal_transaction_tracking_id');
        Pesapal::redirectToCallback($merchant_reference,$tracking_id);
    }

    function handleIPN(Request $request){
        $notification_type = Request::input('pesapal_notification_type');
        $merchant_reference = Request::input('pesapal_merchant_reference');
        $tracking_id = Request::input('pesapal_transaction_tracking_id');
        Pesapal::redirectToIPN($notification_type,$merchant_reference,$tracking_id);
    }

    function test(){
        $details = array( // the defaults will be overidden if set in $params
            'amount' => '10',
            'description' => 'Test',
            'type' => 'MERCHANT',
            'first_name' => 'Tim',
            'last_name' => 'Knox',
            'email' => 'timothyradier@yahoo.com',
            'phonenumber' => '254723238631',
            'live' => true,
            'callback_route' => ''
        );
        $pesapal = new Pesapal;
        //dd($details);
        $pesapal -> makePayment($details);
    }

}