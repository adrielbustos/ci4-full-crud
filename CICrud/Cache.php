<?php

namespace App\Models\CICrud;

class Cache
{

    private static $instances = [];
	private array $objects = [];

	private function __construct()
	{
	}

	public static function getInstance():Cache
    {

        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];

	}
	
	// private function __construct()
    // {
    //     echo 'Hemos creado un nuevo objeto <br />';
    // }

    // public static function getInstance()
    // {
    //     static $instance = null;
    //     if (null === $instance) {
    //         $instance = new static();
    //     } else {
    //         echo 'El objeto ya existe, no puedes volver a crearlo <br />';
    //     }
    //     return $instance;
    // }

	/**
	 * @param object
	 */
	public function addObject(object $object):void{
		$this->objects[] = $object;
	}

	public function getObjectIsExists(string $className, int $idObject){
		foreach ($this->objects as $object){
			if ( get_class($object) === $className AND $object->getId() === $idObject ) return $object;
		}
		return false;
	}

}
