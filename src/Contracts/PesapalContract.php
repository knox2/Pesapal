<?php
/**
 * Created by PhpStorm.
 * User: mxgel
 * Date: 11/15/16
 * Time: 4:47 AM
 */

namespace Knox\Pesapal\Contracts;


/**
 * Interface PesapalContract
 * @package Knox\Pesapal\Contracts
 */
interface PesapalContract
{
    /**
     * Pending payment
     */
    const PESAPAL_STATUS_PENDING = 'pending';

    /**
     * Failed payment
     */
    const PESAPAL_STATUS_FAILED = 'failed';

    /**
     * Successfully completed payment
     */
    const PESAPAL_STATUS_COMPLETED = 'completed';
}