# Pesapal Laravel 5 API
Laravel 5 Package for the Pesapal API

## Installation

### Add this package using Composer

From the command line inside your project directory, simply type:

`composer require knox/pesapal`

### Update your config

Add the service provider to the providers array in config/app.php:

`Knox\Pesapal\PesapalServiceProvider::class,`

Add the facade to the aliases array in config/app.php:

`'Pesapal' => Knox\Pesapal\Facades\Pesapal::class,` 

### Publish the package configuration

Publish the configuration file and migrations by running the provided console command:

`php artisan vendor:publish --provider="Knox\Pesapal\PesapalServiceProvider"`

## Setup
### Environmental Variables
PESAPAL\_CONSUMER\_KEY `pesapal consumer key`<br/>

PESAPAL\_CONSUMER\_SECRET `pesapal consumer secret`<br/>

PESAPAL\_CURRENCY `ISO code for the currency`<br/>

PESAPAL\_IPN `controller method to call for instant notifications IPN  as relative path from App\Http\Controllers\ eg "TransactionController@confirmation"`<br/>

PESAPAL\_CALLBACK_ROUTE `route name to handle the callback eg Route::get('donepayment', ['as' => 'paymentsuccess', 'uses'=>'PaymentsController@paymentsuccess']);  The route name is "paymentsuccess"`<br/>

<b>NB: The controller method accepts 4 function parameters, Example:</b>

```php
public function confirmation($trackingid,$status,$payment_method,$merchant_reference)
{
	$payments = Payments::where('tracking',$trackingid)->first();
    $payments -> payment_status = $status;
    $payments -> payment_method = $payment_method;
    $payments -> save();
}       
```

### Config
<b>live</b> - Live or Demo environment<br/>

The ENV Variables can also be set from here.

## Usage
At the top of your controller include the facade<br/>
`use Pesapal;`

### Example Code...Better Example..Haha
Assuming you have a Payment Model <br/>

```php
use Pesapal;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use App\Payment;

class PaymentsController extends Controller
{
    public function payment(){//initiates payment
        $payments = new Payment;
        $payments -> businessid = Auth::guard('business')->id(); //Business ID
        $payments -> transactionid = Pesapal::random_reference();
        $payments -> status = 'NEW'; //if user gets to iframe then exits, i prefer to have that as a new/lost transaction, not pending
        $payments -> amount = 10;
        $payments -> save();

        $details = array(
            'amount' => $payments -> amount,
            'description' => 'Test Transaction',
            'type' => 'MERCHANT',
            'first_name' => 'Fname',
            'last_name' => 'Lname',
            'email' => 'test@test.com',
            'phonenumber' => '254-723232323',
            'reference' => $payments -> transactionid,
            'height'=>'400px',
            //'currency' => 'USD'
        );
        $iframe=Pesapal::makePayment($details);

        return view('payments.business.pesapal', compact('iframe'));
    }
    public function paymentsuccess(Request $request)//just tells u payment has gone thru..but not confirmed
    {
        $trackingid = $request->input('tracking_id');
        $ref = $request->input('merchant_reference');

        $payments = Payment::where('transactionid',$ref)->first();
        $payments -> trackingid = $trackingid;
        $payments -> status = 'PENDING';
        $payments -> save();
        //go back home
        $payments=Payment::all();
        return view('payments.business.home', compact('payments'));
    }
    //This method just tells u that there is a change in pesapal for your transaction..
    //u need to now query status..retrieve the change...CANCELLED? CONFIRMED?
    public function paymentconfirmation(Request $request)
    {
        $trackingid = $request->input('pesapal_transaction_tracking_id');
        $merchant_reference = $request->input('pesapal_merchant_reference');
        $pesapal_notification_type= $request->input('pesapal_notification_type');

        //use the above to retrieve payment status now..
        $this->checkpaymentstatus($trackingid,$merchant_reference,$pesapal_notification_type);
    }
    //Confirm status of transaction and update the DB
    public function checkpaymentstatus($trackingid,$merchant_reference,$pesapal_notification_type){
        $status=Pesapal::getMerchantStatus($merchant_reference);
        $payments = Payment::where('trackingid',$trackingid)->first();
        $payments -> status = $status;
        $payments -> payment_method = "PESAPAL";//use the actual method though...
        $payments -> save();
        return "success";
    }
}
```
#### Example ENV

```
 PESAPAL_IPN=PaymentsController@paymentconfirmation
 PESAPAL_LIVE=true
 PESAPAL_CALLBACK_ROUTE=paymentsuccess
```
#### Example Routes
Relevant routes example, to help exclude entire webhooks route group in Csrf check in VerifyCsrfToken Middleware<br/>

```php
Route::group(['prefix' => '/webhooks'], function () {
    //PESAPAL
    Route::get('donepayment', ['as' => 'paymentsuccess', 'uses'=>'PaymentsController@paymentsuccess']);
    Route::get('paymentconfirmation', 'PaymentsController@paymentconfirmation');
});
 ```
 
#### All Done
Feel free to report any issues


