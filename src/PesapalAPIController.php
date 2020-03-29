<?php

namespace Knox\Pesapal;

use Knox\Pesapal\Exceptions\PesapalException;
use App\Http\Controllers\Controller;
use Pesapal;

class PesapalAPIController extends Controller
{

    function handleCallback()
    {
        $merchant_reference = request('pesapal_merchant_reference');
        $tracking_id = request('pesapal_transaction_tracking_id');
        $route = config('pesapal.callback_route');
        return redirect()->route(
            $route,
            array('tracking_id' => $tracking_id, 'merchant_reference' => $merchant_reference)
        );
    }

    function handleIPN()
    {
        if (request('pesapal_notification_type') && request('pesapal_merchant_reference') && request('pesapal_transaction_tracking_id')) {
            $notification_type = request('pesapal_notification_type');
            $merchant_reference = request('pesapal_merchant_reference');
            $tracking_id = request('pesapal_transaction_tracking_id');
            Pesapal::redirectToIPN($notification_type, $merchant_reference, $tracking_id);
        } else {
            throw new PesapalException("incorrect parameters in request");
        }
    }
    // Test bleeding edge
}
