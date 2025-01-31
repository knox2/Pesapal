<?php

namespace Knox\Pesapal;

use Knox\Pesapal\Exceptions\PesapalException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pesapal;

class PesapalAPIController extends Controller
{

    function handleCallback(Request $request)
    {
        $merchant_reference = $request->input('OrderMerchantReference');
        $tracking_id = $request->input('OrderTrackingId');
        $route = config('pesapal.callback_route');
        return redirect()->route(
            $route,
            array('tracking_id' => $tracking_id, 'merchant_reference' => $merchant_reference)
        );
    }

    function handleIPN(Request $request)
    {
        if ($request->input('OrderNotificationType') && $request->input('OrderMerchantReference') && $request->input('OrderTrackingId')) {
            $notification_type = $request->input('OrderNotificationType');
            $merchant_reference = $request->input('OrderMerchantReference');
            $tracking_id = $request->input('OrderTrackingId');
            Pesapal::redirectToIPN($notification_type, $merchant_reference, $tracking_id);
        } else {
            throw new PesapalException("incorrect parameters in request");
        }
    }
    // Test bleeding edge
}
