# Pesapal Laravel 5 API
Laravel 5 Package for the Pesapal API

##Installation

###Add this package using Composer

From the command line inside your project directory, simply type:

`composer require knox/pesapal`

###Update your config

Add the service provider to the providers array in config/app.php:

`Knox\Pesapal\PesapalServiceProvider::class,`

Add the facade to the aliases array in config/app.php:

`'Pesapal' => Knox\Pesapal\Facades\Pesapal::class,` 

###Publish the package configuration

Publish the configuration file and migrations by running the provided console command:

`php artisan vendor:publish --provider="Knox\Pesapal\PesapalServiceProvider"`

##Setup
###Environmental Variables
PESAPAL\_CONSUMER\_KEY `pesapal consumer key`<br/>

PESAPAL\_CONSUMER\_SECRET `pesapal cosumer secret`<br/>

PESAPAL\_CURRENCY `ISO code for the currency`<br/>

PESAPAL\_IPN `controller method to call for instant notifications IPN eg TransactionController@confirmation`<br/>

PESAPAL\_CALLBACK_ROUTE `route name to handle the callback eg Route::get('pesapal-test', ['as' => 'test', 'uses'=>'PaymentsController@test']);  The route name is "test"`<br/>

<b>NB: The controller method accepts 4 function parameters, Example:</b>

```
public function confirmation($trackingid,$status,$payment_method,$merchant_reference)
{
	$payments = Payments::where('tracking',$trackingid)->first();
    $payments -> payment_status = $status;
    $payments -> payment_method = $payment_method;
    $payments -> save();
}       
```

###Config
<b>live</b> - Live or Demo environment<br/>

The ENV Variables can also be set from here.

##Usage
At the top of your controller include the facade<br/>
`use Pesapal;`

###Example Code
Assuming you have a Payment Model <br/>

```
public function payment(){     
    $payments = new Payments;
	$payments -> order_id = mt_rand(1,1000);
    $payments -> user_id = Auth::id();
    $payments -> transaction = Pesapal::random_reference();
    $payments -> payment_status = 'PENDING';
    $payments -> amount = 10;
    $payments -> save();
    $details = array(
        'amount' => $payments -> amount,
        'description' => 'Test',
        'type' => 'MERCHANT',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@google.com', 
        'phonenumber' => '254723232323',
        'reference' => $payments -> transaction,
        //'currency' => 'USD'
    );
    return Pesapal::makePayment($details);
}
```
Returns an IFRAME to display the payment options
<br/>

The Method receives two input arguments<br/><br/>
<b>Example implementation</b><br/>

```
public function paid()
{
    $trackingid = Input::get('tracking_id');
    $ref = Input::get('merchant_reference');
	$payments = Payments::where('transaction',$ref)->first();
    $payments -> tracking = $trackingid;
    $payments -> save();
    return view('payment', ['trackingid' => $trackingid, 'ref' => $ref]);
}
```

####All Done
Feel free to report any issues


