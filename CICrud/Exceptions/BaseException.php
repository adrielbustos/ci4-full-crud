<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class BaseException extends \Exception implements ErrorCodes {

    /**
     *
     * Set the type error
     *
     * @var string
     */
    protected string $typeError = 'UndefinedException';

    public function getTypeError():string
    {
        return $this->typeError;
    }

}