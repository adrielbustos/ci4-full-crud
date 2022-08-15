<?php

namespace App\Models\CICrud\Exceptions;

use App\Models\CICrud\ErrorCodes;

class QueryParamsError extends BaseException
{
    public function __construct (string $param, string $model, bool $formatError = false)
    {
        if (!$formatError)
        {
            $msg = "Query param: $param not exists in model $model";
        }
        else
        {
            $msg = "El formato enviado para $param es erroneo";
        }
        parent::__construct($msg, ErrorCodes::QUERYPARAMSERROR);
        $this->typeError = 'QueryParamsError';
    }
}
