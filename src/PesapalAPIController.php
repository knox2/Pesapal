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
        $route = Session::get('pesapal_callback_route');
        return redirect()->route($route,array('tracking_id' => $tracking_id,'merchant_reference' => $merchant_reference));
    }

    function handleIPN(){
        if(Input::has('pesapal_notification_type') && Input::has('pesapal_merchant_reference') && Input::has('pesapal_transaction_tracking_id')){
            $notification_type = Input::get('pesapal_notification_type');
            $merchant_reference = Input::get('pesapal_merchant_reference');
            $tracking_id = Input::get('pesapal_transaction_tracking_id');
            Pesapal::redirectToIPN($notification_type,$merchant_reference,$tracking_id);
        }
        else{
            echo "Incorrect Parameters";
        }
    }

}