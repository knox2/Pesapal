<?php

Route::get('pesapal-callback',['as'=>'pesapal-callback', 'uses'=>'knox\pesapal\PesapalAPIController@handleCallback']);
Route::get('pesapal-ipn', ['as'=>'pesapal-ipn', 'uses'=>'knox\pesapal\PesapalAPIController@handleIPN']);