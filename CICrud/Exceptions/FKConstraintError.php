<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class FKConstraintError extends BaseException
{

    public function __construct(string $message)
    {

        parent::__construct($message, ErrorCodes::FKCONSTRAINERROR);
        $this->typeError = 'FKConstraintError';

    }

}