<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class UniqueError extends BaseException
{

    public function __construct(array $uniques)
    {

        $uniques = implode(', ', $uniques);
        $message = 'Los campos (' . $uniques . ') ya se encuentran registrados';
        parent::__construct($message, ErrorCodes::UNIQUEERROR);
        $this->typeError = 'UniqueError';

    }

}