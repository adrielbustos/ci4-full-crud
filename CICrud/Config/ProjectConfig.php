<?php

namespace App\Models\CICrud\Config;

interface ProjectConfig
{
    public const limitRecursiveSearch = 1;
    public const getRelations = true;
    public const getArraySubModels = true;
    public const defaultLimitCount = 50;
    public const defaultStartIn = 10;
}
