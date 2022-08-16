<?php

namespace App\Models\CICrud\Config;

interface ProjectConfig
{
    /**
     * Use this variable to find objects into the principal object.
     * This is recursive, so if the value is 1, the ORM only search for 1 sub model if exists.
     * If the value is more of 1, the ORM search models into the models relations
     * @var bool
     */
    public const limitRecursiveSearch = 1;
    public const getRelations = true;
    public const getArraySubModels = true;
    public const defaultLimitCount = 50;
    public const defaultStartIn = 0;
}
