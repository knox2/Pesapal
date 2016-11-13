<?php  
 
return [    
 
'consumer_key' => env('PESAPAL_CONSUMER_KEY'),

'consumer_secret' => env('PESAPAL_CONSUMER_SECRET') ,

'currency' => env('PESAPAL_CURRENCY', 'KES'),

'ipn' => env('PESAPAL_IPN'),

'live' => env('PESAPAL_LIVE', true),

'callback_route' => env('PESAPAL_CALLBACK_ROUTE'),
 
];
 
?>