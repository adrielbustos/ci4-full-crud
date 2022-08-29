<?php

namespace App\Models\CICrud\Config;
use App\Models\CICrud\CICrud;

class Config implements ProjectConfig {

    /**
     * Determines if can search internal models
     * @var int
     */
    public int $limitRecursiveSearch = ProjectConfig::limitRecursiveSearch;

    /**
     * Used for know the actual recursive sub model
     * @var int
     */
    private int $_recursiveNow = 0;


    /**
     * Search for N to N relations
     * @var bool
     */
    public bool $getRelations = ProjectConfig::getRelations;


    /**
     * Search for ONE to N relations
     * @var bool
     */
    public bool $getArraySubModels = ProjectConfig::getArraySubModels;


    /**
     * They are used to control the actual attribute and not generate an infinity bucle
     * @var string
     */
    private string $attrScape = '';
    private bool $isGetAttrScape = false;


    /**
     * Types of model name formats
     * @var array
     */
    private const modelNameFormatTypes = [
        'model',
        'small',
        'class'
    ];


    /**
     * Path to models folder
     * DEBE TERMINAR CON /
     * @var string
     */
    public const modelsFolder = '';

    /**
     * FConfig constructor.
     */
    public function __construct()
    {
        if ($this->limitRecursiveSearch < 0) $this->limitRecursiveSearch = 1;
    }

    public function canAddRecursive(): bool
    {
        if ($this->_recursiveNow <= self::limitRecursiveSearch)
        {
            $this->_recursiveNow++;
            return true;
        }
        return false;
    }

    public function subRecursive(): void
    {
        if ($this->_recursiveNow > 0) $this->_recursiveNow--;
    }

    public function resetRecursive(): void
    {
        $this->_recursiveNow = 0;
    }

    public function setAttrEscape(string $attrScape)
    {
        $this->attrScape = strtolower(str_replace('Model', '', $attrScape));
    }

    public function getAttrEscape():string
    {
        return $this->attrScape;
    }

    public function setIsGetAttrScape(bool $isGet):void
    {
        $this->isGetAttrScape = $isGet;
    }

    public function getIsSetAttrScape():bool
    {
        return $this->isGetAttrScape;
    }

    /**
     * Format de model name to necessary.
     * @param string $modelName
     * @param string $formatType
     * @return string
     */
    public static function formatModelName(string $modelName, string $formatType = 'model'):string
    {

        if (!in_array($formatType, self::modelNameFormatTypes)){
            return $modelName;
        }

        $modelNameFormatted = '';

        switch ($formatType) {
            case 'model':
                $modelNameFormatted = ucwords($modelName).'Model';
                break;
            case 'small':
                $modelName = str_replace('Model', '', $modelName);
                $modelNameFormatted = strtolower($modelName);
                break;
            case 'class':
                $modelNameFormatted = str_replace('Model', '', $modelName);
                break;
        }

        return $modelNameFormatted;

    }

    /**
     *
     * Validate if the object has the config send.
     *
     * @param object $object
     * @param string $formatType
     * @return string
     */
    public static function hasConfig(CICrud $object, string $configName):bool
    {

        if ( !property_exists($object, $configName) ) {
            return false;
        }

        $hasConfig = true;

        if ( !key_exists($configName, $object::config) ) {
            $hasConfig = false;
        } else {

            $hasConfig = false;

        }

        if ( $hasConfig ) {

            if ( $object::config[$configName] === [] ) {
                $hasConfig = false;
            }

        }

        return $hasConfig;

    }

}
