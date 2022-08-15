<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class ValueNotNull extends BaseException
{

    public function __construct(string $notNulls)
    {

        $message = 'Los campos (' . $notNulls . ') no pueden ser nulos';
        parent::__construct($message, ErrorCodes::VALUENOTNULL);
        $this->typeError = 'ValueNotNull';

    }

}