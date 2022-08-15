<?php

namespace App\Models\CICrud;

class FTools {

    /**
     * FTools constructor.
     */
    public function __construct() {}


    public static function getClassNS(object $class, bool $whitModel = true):string
    {
        $modelName = (new \ReflectionClass($class))->getShortName();
        if ($whitModel) return $modelName;
        return str_replace('Model', "", $modelName);
    }

    /**
     *
     * Convierte un string a camel case
     *
     * @param 	string $class
     * @param 	bool $nameModel
     *
     * @return 	string
     */
    public static function convertClassNameToModelName(string $class, bool $modelEnd = true):string
    {

        $name = '';

        if (strpos($class, '_') !== FALSE) {
            $subNames = explode('_', $class);
            foreach ($subNames as $subName) {
                $name .= ucwords($subName);
            }
        } else {
            $name .= ucwords($class);
        }

        if ( $modelEnd ) {
            $name .= 'Model';
        }

        return $name;

    }

    public static function formatTableName(string $class):string
    {
        return strtolower($class);
    }

    public static function getNtoNModelName(string $name1, string $name2, string $type = 'model'):string
    {

        $f = ''; // first name
        $s = ''; // second name

        if (strlen($name1)>strlen($name2))
        {
            $f = $name1;
            $s = $name2;
        }
        elseif (strlen($name1)<strlen($name2))
        {
            $f = $name2;
            $s = $name1;
        }
        else {
            $names[] = $name1;
            $names[] = $name2;
            sort($names);
            $f = $names[0];
            $s = $names[1];
        }

        if ($type === 'table')
        {
            return strtolower($f).'_'.strtolower($s).'_n_n';
        }

        return ucwords($f).ucwords($s).'Model';

    }


}
