<?php namespace Knox\Pesapal\Facades;

use Illuminate\Support\Facades\Facade;

class Pesapal extends Facade {
    protected static function getFacadeAccessor() { 
    	return 'pesapal'; 
    	}
}