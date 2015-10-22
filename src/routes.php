<?php
Route::get('pesapal-callback', 'knox\pesapal\PesapalAPIController@handleCallback');
Route::get('pesapal-ipn', 'knox\pesapal\PesapalAPIController@handleIPN');
Route::get('pesapal-test', 'knox\pesapal\PesapalAPIController@test');
