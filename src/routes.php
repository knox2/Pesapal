<?php

Route::get('pesapal-callback',['as'=>'pesapal-callback', 'uses'=>'Knox\Pesapal\PesapalAPIController@handleCallback']);
Route::get('pesapal-ipn', ['as'=>'pesapal-ipn', 'uses'=>'Knox\Pesapal\PesapalAPIController@handleIPN']);