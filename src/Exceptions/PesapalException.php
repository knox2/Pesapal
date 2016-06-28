<?php

namespace Knox\Pesapal\Exceptions;

use Exception;

class PesapalException extends Exception
{
     
    public function __construct($message)
    {
        parent::__construct($message);
    }

}