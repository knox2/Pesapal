<?php
//use App\Http\Controllers\Controller; 

Route::get('pesapal-callback',['as'=>'pesapal-callback', 'uses'=>'knox\pesapal\PesapalAPIController@handleCallback']);
Route::get('pesapal-ipn', 'knox\pesapal\PesapalAPIController@handleIPN');
Route::get('pesapal-test', 'knox\pesapal\PesapalAPIController@test');
Route::get('pesapal-paid', ['as'=>'after-payment','uses'=>'knox\pesapal\PesapalAPIController@paid']);

Route::get('test', ['as'=>'test','uses'=>'knox\pesapal\PesapalAPIController@tests']);