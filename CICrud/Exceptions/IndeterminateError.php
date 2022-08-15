<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class IndeterminateError extends BaseException
{

    public function __construct()
    {

        //$last_query = $this->db->last_query();
        $last_query = 'sdafasdf';
        parent::__construct('IndeterminateError', ErrorCodes::INDETERMINATEERROR);
        log_message('info', 'IndeterminateError: ' . $last_query);
        $this->typeError = 'IndeterminateError';

    }

}