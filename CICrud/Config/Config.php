<?php

namespace App\Models\CICrud\Config;

class Config implements ProjectConfig {

    /**
     * DETERMINA SI SE REALIZA LA BUSQUEDA DE OBJETOS INTERNOS
     * @var bool
     */
    public bool $recursiveSearch = ProjectConfig::limitRecursiveSearch;


    /**
     * LIMITE DEL RANGO DE BUSQUEDA DE LOS MODELOS INTERNOS
     * @var int
     */
    public int $limitRecursiveSearch = ProjectConfig::limitRecursiveSearch;

    private int $_recursiveNow = 0;


    /**
     * BUSCAR MODELOS RELACIONALES DE TIPO N A N
     * @var bool
     */
    public bool $getRelations = ProjectConfig::getRelations;


    /**
     * BUSCAR LOS MODELOS RELACIONALES DE TIPO UNO A N
     * @var bool
     */
    public bool $getArraySubModels = ProjectConfig::getArraySubModels;


    /**
     * ATRIBUTO QUE SE USA PARA NO GENERAR UN BULCE INFINITO
     * @var string
     */
    private string $attrScape = '';


    /**
     * ATRIBUTO QUE SE USA PARA NO GENERAR UN BULCE INFINITO
     * @var boolean
     */
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
     * List of config available in Fireback
     *
     * @var array
     *
     */
    private const configsAvailable = [
        'nTon' => TRUE,
        'canNull' => TRUE,
        'unique' => TRUE,
        'index' => TRUE,
        'parentsObjects' => TRUE,
        'default' => TRUE,
        'isDate' => TRUE,
        'isTime' => TRUE, // TODO FALTA IMPLEMENTAR
        'isDatetime' => TRUE // TODO FALTA IMPLEMENTAR
    ];


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
        $this->attrScape = strtolower(str_replace('Model', '', $attrScape));;
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
    public static function hasConfig(object $object, string $nameConfig):bool
    {

        if ( !in_array($nameConfig, self::configsAvailable) ) {
            return false;
        }

        $hasConfig = true;

        if ( defined( get_class($object).'::config' ) ){

            if ( !key_exists($nameConfig, $object::config) ) {
                $hasConfig = false;
            }

        } else {

            $hasConfig = false;

        }

        if ( $hasConfig ) {

            if ( $object::config[$nameConfig] === [] ) {
                $hasConfig = false;
            }

        }

        return $hasConfig;

    }

}
