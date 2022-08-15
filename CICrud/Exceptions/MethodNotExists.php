<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class MethodNotExists extends BaseException
{

    public function __construct(string $class, string $method)
    {

        $message = 'La clase ' . $class . ' no tiene el metodo obligatorio:' . $method . '(). Esto puede darse a que su modelo de dominio no esta correctamente relacionado, por lo que se recomienda que lo revise.';
        parent::__construct($message, ErrorCodes::METHODNOTEXIST);
        $this->typeError = 'MethodNotExists';

    }

}