<?php

namespace App\Models\CICrud\Exceptions;
use App\Models\CICrud\ErrorCodes;

class FKDeleteError extends BaseException
{

    public function __construct(string $id, string $table)
    {

        $message = 'El registro con id (' . $id . ') de la tabla ' . $table . ' no puede ser eliminado por restricciones de claves foraneas';
        parent::__construct($message. ErrorCodes::FKDELETEERROR);
        $this->typeError = 'FKDeleteError';

    }

}