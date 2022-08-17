<?php

namespace App\Models\CICrud;

use App\Models\CICrud\QueryBuldier\QueryBuldier;
use App\Models\CICrud\Config\Config;

use App\Models\CICrud\Exceptions\FKConstraintError;
use App\Models\CICrud\Exceptions\IndeterminateError;
use App\Models\CICrud\Exceptions\MethodNotExists;
use App\Models\CICrud\Exceptions\UniqueError;
use App\Models\CICrud\Exceptions\ValueNotNull;
use App\Models\CICrud\Exceptions\FKDeleteError;
use App\Models\CICrud\Exceptions\QueryParamsError;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

use Config\Database;
use Error;
use mysqli_sql_exception;

abstract class CICrud
{

    /**
     * 
     * Table model name
     * 
     * @var string
     */
    protected string $table = '';

    /**
     * @var ModelConfig
     */
    protected ModelConfig $modelConfig;

    /**
     *
     * Load the project config
     *
     * @var Config
     */
    private Config $config;

    /**
     *
     * DB Connector
     *
     * @var BaseConnection
     */
    private BaseConnection $db;

    /**
     *
     * Query Builder
     *
     */
    private BaseBuilder $builder;

    /**
     *
     * Secondary Query Builder
     *
     */
    private QueryBuldier $queryBuldier;

    /**
     *
     * Arreglo de strings que contienen los nombres de las clases que no se van a tener en cuenta
     *
     * @var array
     */
    private const attrScape = [
        'BaseConnection',
        'Config',
        'BaseBuilder',
        'Connection',
        'Builder'
    ];


    /**
     * CICrud constructor.
     * @param Config|null $Config
     */
    protected function __construct (Config $Config = null)
    {

        if ($this->table == "") {
            throw new Error("The table for the Model can not be a empty string");
            // throw new Error("The table for the Model " + get_class() + " can not be a empty string");
        }

        $this->db = Database::connect();
        $this->modelConfig = new ModelConfig();

        if (is_null($Config)) {
            $this->config = new Config();
        } else {
            $this->config = $Config;
        }

    }


    /**
     *
     * Obtiene un objeto del modelo definido con sus realaciones de tipo HAS MANY.
     * Solo el atributo id es requerido.
     *
     * @param CICrud $object
     * @return CICrud
     */
    protected function getObject (CICrud $object): CICrud
    {

        $idToGet = $object->getId();
        $nameOfClass = get_class($object);

        if ($idToGet === NULL || $idToGet === 0) return new $nameOfClass();

        $this->builder = $this->db->table($this->table);
        $this->builder->where('id', $idToGet);
        $result = $this->builder->get()->getResult();

        if (!$result) return new $nameOfClass();

        if (!$this->config->getIsSetAttrScape())
        {
            $this->config->setAttrEscape(get_class($object));
            $this->config->setIsGetattrScape(true);
        }

        if ($this->config->getRelations)
        {
            $this->_setConfig($this->table, $object->modelConfig, $result[0]);
        }

        return $this->_convertObject(get_class($object), $result[0]);

    }


    /**
     *
     * GET'S
     *
     * Metodo que busca todos los objetos, con todas sus relaciones.
     * No hay atributos que sean requeridos dentro del objeto.
     *
     * @param CICrud $object El objeto el cual se va a buscar
     * @param int $limitCount (no obligatorio) Limite de resultados a buscar
     * @param int $startIn (no obligatorio) Inicio del conteo
     * @return    array           Objetos convertidos
     * @throws QueryParamsError
     */
    protected function getAllObjects (CICrud $object, int $limitCount = 10, int $startIn = 0, string $orderBy = ""): array
    {

        $this->builder = $this->db->table($this->table);
        $arrayQueryParams = self::_getQueryOrderBy($object, $orderBy);
        foreach ($arrayQueryParams as $queryParamKey => $queryParamValue) {
            $this->builder->orderBy($queryParamKey, $queryParamValue);
        }
        // if (!$arrayQueryParams) $this->querySelect = $this->builder->orderBy('id', 'DESC'); // TODO habilitar?

        $result = $this->builder->get($limitCount, $startIn)->getResult();

        if (!$result) {
            return [];
        }

        $this->config->setAttrEscape(get_class($object));

        $arrayObjects = [];

        foreach ($result as $resultArray) {

            $resultObject = (object)$resultArray;

            if ($this->config->getRelations) {
                $this->_setConfig($this->table, $object->modelConfig, $resultObject);
            }

            $arrayObjects[] = $this->_convertObject(get_class($object), $resultObject);

        }

        return $arrayObjects;

    }


    /**
     * @param CICrud $object
     * @param int $limitCount
     * @param int $startIn
     * @param string $orderBy
     * @return array
     * @throws QueryParamsError
     */
    protected function getObjectWhere (CICrud $object, int $limitCount = 10, int $startIn = 0, string $orderBy = ""): array
    {

        $this->builder = $this->db->table($this->table);

        $this->builder->select($this->table . '.*');

        $arrayQueryParams = self::_getQueryOrderBy($object, $orderBy);
        foreach ($arrayQueryParams as $queryParamKey => $queryParamValue) {
            $this->builder->orderBy($queryParamKey, $queryParamValue);
        }
        // if (!$arrayQueryParams) $this->querySelect = $this->builder->orderBy('id', 'DESC'); // TODO habilitar?

        $this->builder->limit($limitCount, $startIn);

        $this->_innitWhereConnection();

        foreach ($object as $key => &$value) {

            if (!(bool)$value) continue;

            switch (gettype($value)) {

                case 'object':

                    if (!self::_isValidObject($value)) break;

                    //if ( Config::recursiveSearch ) {
                    //    $this->_setInnerJoin($value, $this->table);
                    //}

                    $this->_setInnerJoin($value, $this->table);

                    break;

                case 'string':

                    $this->builder->like($this->table . "." . $key, $value);

                    break;

                case 'array':

                    if ($this->config->getArraySubModels || $this->config->getRelations) {
                        $this->_setDefinedRelation($object, $key);
                    }

                    break;

                default:

                    $this->builder->where($this->table . "." . $key, $value);

                    break;
            }

        }

        $this->queryBuldier->setRelations();

        //print_r($this->db->get_compiled_select($this->table));die;

        $results = $this->builder->get()->getResult();

        if ($results) {

            $arrayObjects = [];

            foreach ($results as $result) {

                if ($this->config->getRelations) {
                    $this->_setConfig($this->table, $object->modelConfig, $result);
                }

                $arrayObjects[] = $this->_convertObject(get_class($object), $result);

            }

            return $arrayObjects;

        }

        return [];

    }


    /**
     *
     * Metodo que guarda en base de datos el objeto enviado
     * Para guardar los objetos relaciones solo es necesario que esten sus atributos id seteados.
     *
     * @param CICrud $object $object            (object) El objeto el cual se va a guardar
     * @return    int             Id de la fila insertada - 0 (cero) en caso de error
     * @throws FKConstraintError
     * @throws IndeterminateError
     * @throws MethodNotExists
     * @throws UniqueError
     * @throws ValueNotNull
     */
    protected function saveObject (CICrud $object): int
    {

        $this->builder = $this->db->table($this->table);

        $nToNRelation = [];
        $oneToMuchRelation = [];

        $attrToSave = [];

        $manyToMany = $object->modelConfig->getNtoN();
        $compositionConfig = $object->modelConfig->getParentsObjects();

        foreach ($object as $key => $value) {

            if (is_null($value)) {
                continue;
            }

            switch (gettype($value)) {

                case 'string':

                    if ($value !== '') {
                        $attrToSave[$key] = $value;
                    } else {
                        $attrToSave[$key] = NULL;
                    }

                    break;

                case 'array':

                    if (in_array($key, $manyToMany) && count($value) > 0) {
                        $nToNRelation[$key] = $value;
                    } elseif (count($value) > 0) {
                        $oneToMuchRelation[$key] = $value;
                    }

                    break;

                case 'object':

                    if (
                        !method_exists($value, 'getId') or
                        in_array(get_class($value), self::attrScape)
                    ) {
                        break;
                    }

                    if ($compositionConfig && in_array($key, $compositionConfig)) {

                        $hasIdParent = $value->getId();

                        if (!(bool)$hasIdParent) { // ESTA DEFINIDO EL ID DE NUESTRO PADRE?
                            $idSaved = $value->keep();
                        } else {
                            $idSaved = $hasIdParent;
                        }

                        if (!(bool)$idSaved) {
                            // throw error?
                            print_r('error al guardar: ');
                            print_r($value);
                            die;
                        } else {
                            $attrToSave['id_' . $key] = $idSaved;
                        }

                        break;

                    }

                    $attrToSave['id_' . $key] = $this->$key->getId();

                    break;

                default:
                    $attrToSave[$key] = $value;
                    break;

            }

        }

        if (!$attrToSave) {
            print_r('Error no hay nada que insertar');
            die; // TODO realizar mensaje a mostrar
        }

        try {

            $this->builder->insert($attrToSave);

            $object->setId($this->db->insertID());

            if ($nToNRelation) {

                foreach ($nToNRelation as $nTn) {
                    $this->_savesNToN($nTn, $object);
                }

            }

            if ($oneToMuchRelation) {

                foreach ($oneToMuchRelation as $attr => $values_attr) {
                    $this->_savesOneToMuch($values_attr, $attr, $object);
                }

            }

            return $object->getId();

        } catch (mysqli_sql_exception $MYSQLError) {

            $errors = $this->db->error();
            $code = intval($errors['code']);
            $message = $errors['message'];

            switch ($code) {

                case 1452:

                    $reference = explode(') REFERENCES `', $message);
                    $reference = $reference[1];
                    $reference = explode(' ', $reference);
                    $reference = $reference[0];

                    $reference = substr($reference, 0, -1);

                    $getMethod = 'get' . ucfirst($reference);
                    $id = $object->$getMethod()->getId();

                    $message = 'Error de clave foranea: ' . $reference . ' no contiene el id ' . $id;
                    throw new FKConstraintError($message);

                case 1062:

                    $uniques = explode("'", $message);
                    $uniques = explode("-", $uniques[1]);

                    throw new UniqueError($uniques);

                case 1048:

                    $notNull = explode(" ", $message);

                    throw new ValueNotNull($notNull[1]);

                default:
                    throw new IndeterminateError();
            }

        }

    }


    /**
     *
     * -------------------------------------------------------------------------
     * UPDATE`S
     * -------------------------------------------------------------------------
     * @throws MethodNotExists
     */
    protected function updateObject (CICrud $object): bool
    {

        if (!method_exists($object, 'getId')) {
            throw new MethodNotExists(get_class($object), 'getId');
        }

        $idToGet = $object->getId();

        if (!(bool)$idToGet) {
            return false;
        }

        $this->builder = $this->db->table($this->table);

        $attrToUpdate = [];

        $table = $this->table;

        $manyToMany = $object->modelConfig->getNtoN();
        $compositionConfig = $object->modelConfig->getParentsObjects();

        foreach ($object as $key => $value) {

            if (($value === NULL) || ($value === '') || $key === 'id') { // TODO REVISAR LOS CAMPOS DE TIPO STRIN VACIOS
                continue;
            }

            switch (gettype($value)) {

                case 'array':

                    if ($manyToMany && in_array($key, $manyToMany)) { // SI EL ATRIBUTO PERTENECE A LA CONFIGURACION DE N A N
                        $modelNToN = array_keys($manyToMany, $key); // OBTENEMOS EL NOMBRE DEL MODELO A INSERTAR
                        $this->_updateNToN($table, $idToGet, $value, $modelNToN[0], $key); // TODO REVISAR
                    } else {
                        $relationalModel = Config::formatModelName($key);
                        $this->_updateNToN($table, $idToGet, $value, $relationalModel, $key); // TODO REVISAR
                    }

                    break;

                case 'object':

                    if (!$value instanceof CICrud) break;

                    if ($compositionConfig && in_array($key, $compositionConfig)) {

                        if (!$value->modify()) {
                            print_r('error al actualizar: ');
                            print_r($value);
                            die;
                        }

                        break;

                    }

                    //echo json_encode(gettype($value));die;

                    if (!method_exists($value, 'getId')) {
                        throw new MethodNotExists(get_class($value), 'getId');
                    }

                    $idValue = $value->getId();

                    if (!(bool)$idValue) {
                        break;
                    }

                    $attrToUpdate['id_' . $key] = $value->getId();

                    break;

                default:
                    $attrToUpdate[$key] = $value;
                    break;

            }

        }

        if (!$attrToUpdate) {
            return TRUE;
        }

        foreach ($attrToUpdate as $attr => $value_attr) {
            $this->builder->set($attr, $value_attr);
        }

        $this->builder->where('id', $idToGet);
        // print_r( $this->db->get_compiled_update($this->table) );die;

        if ($this->builder->update()) {
            return TRUE;
        } else {
            return FALSE;
        }

    }


    /**
     *
     * -------------------------------------------------------------------------
     * DELETE`S
     * -------------------------------------------------------------------------
     * @throws MethodNotExists|FKDeleteError
     */
    protected function deleteObject (CICrud $object): bool
    {

        if (!method_exists($object, 'getId')) {
            throw new MethodNotExists(get_class($object), 'getId');
        }

        $idToDelete = $object->getId();

        if (!(bool)$idToDelete) {
            return FALSE;
        }

        $this->builder = $this->db->table($this->table);

        $object = $object->obtain();

        $hasParent = Config::hasConfig($object, 'parentsObjects'); // VERIFICAMOS SI HAY CONPOSICIONES DENTRO DE OBJETO
        $serializeObject = serialize($object);

        if ($hasParent) {

            $compositionConfig = $object::config['parentsObjects'];

            $parentName = $compositionConfig[0];
            $getMethod = 'get' . ucwords($parentName);
            $parentObject = $object->$getMethod();

            if (!$parentObject->remove($parentObject->getid())) {
                print_r('Error al eliminar la composicion: ' . $this->table);
                die;
                //return FALSE;
            } else {
                log_message('info', 'Deleted object: ' . $serializeObject);
                return TRUE;
            }

        }

        // SELECT * FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC WHERE CONSTRAINT_SCHEMA = 'oncascade' AND REFERENCED_TABLE_NAME = 'user_son'

        $this->builder->where('id', $idToDelete);

        try {

            $this->builder->delete();

            log_message('info', 'Deleted object: ' . $serializeObject);

            return TRUE;

        } catch (mysqli_sql_exception $MYSQLError) {

            log_message('info', 'ERROR TO Deleted object: ' . $serializeObject);

            $errors = $this->db->error();
            $code = intval($errors['code']);
            // $message = $errors['message'];

            switch ($code) {

                case 1451:

                    throw new FKDeleteError($idToDelete, $this->table);

                default:
                    print_r('Errores no controlados: ' . PHP_EOL);
                    print_r($errors);
                    die;
                // break;
            }

            //return FALSE;

        }

    }

    /**
     * -------------------------------------------------------------------------
     * FUNCIONES PRIVADAS
     * -------------------------------------------------------------------------
     */


    /**
     * @param CICrud $object
     * @param string $orderByQuery
     * @return array<string, string>
     * @throws QueryParamsError
     */
    private static function _getQueryOrderBy (CICrud $object, string $orderByQuery = ""): array
    {
        // (ej:?orderBy=id:asc|name:desc|dateUp:desc)
        $query = [];
        $allOrderBy = explode("|", $orderByQuery);
        if ($allOrderBy[0] === "") {
            return [];
        }
        foreach ($allOrderBy as $orderBy) {
            $orderByValues = explode(":", $orderBy);
            if ($orderByValues[0] === "") {
                throw new QueryParamsError($orderBy, "", true);
            }
            if (!property_exists($object, $orderByValues[0])) {
                throw new QueryParamsError($orderByValues[0], FTools::getClassNS($object, false));
            }
            $query[$orderByValues[0]] = $orderByValues[1];

        }
        return $query;
    }

    /**
     *
     * REALIZA LA BUSQUEDA Y LLENA EL OBJETO CON SUS RELACIONES HAS MANY.
     *
     * @param string $table Tabla del objeto que realiza la busqueda
     * @param array $configObject The config model
     * @param object $object El objeto result el cual tiene la informacion
     *
     * @return void
     */
    private function _setConfig (string $table, ModelConfig $configObject, object $object): void
    {

        foreach ($configObject as $keyConfig => $valueConfig) {

            switch ($keyConfig) {

                case 'n_n':

                    foreach ($valueConfig as $model => $columnNToN) {

                        /**
                         * @var CICrud $relationalModel
                         */
                        $relationalModel = model($this->config::modelsFolder . $model, false);
                        $tableNtoN = $relationalModel->getTable();

                        $query = "SELECT `$columnNToN`.*";
                        $query .= "FROM `$tableNtoN`";
                        $query .= "INNER JOIN `$columnNToN` ON `$tableNtoN`.`id_$columnNToN` = `$columnNToN`.`id`";
                        $query .= "WHERE `$tableNtoN`.`id_$table` = " . $object->getId();

                        $nToNResult = $this->db->query($query);

                        $object->$columnNToN = $nToNResult->getResult(); // TODO REVISAR SI HAY QUE CONVERTIRLO A FMODEL

                    }

                    break;

            }

        }

    }


    /**
     *
     * Metodo que convierte un std class en un tipo de objeto modelo definido
     *
     * @param string $destination Nombre de la clase a la cual se quiere obtener un objeto
     * @param object $sourceObject El objeto StdClass el cual se va a convertir
     * @param bool $needImportModel Indica si es necesario importar el modelo
     *
     * @return CICrud Objeto convertido
     */
    private function _convertObject (string $destination, object $sourceObject, bool $needImportModel = false): CICrud
    {

        if ($needImportModel) $destination .= 'Model';

        $destination = model($this->config::modelsFolder . $destination, false);

        $attrToDelete = $this->config->getAttrEscape();
        unset($destination->$attrToDelete);

        foreach ($destination as $nameAttr => $valueAttr)
        {

            $type = gettype($valueAttr);

            switch ($type) {

                case 'array':
                    // TODO REVISAR PORQUE HAY QUE HACER UN GET WHERE DE LOS FILTROS PARA OBTENER EL ARRAY COMPLETO Y VALIDAR SI EXISTE EL ID_ EN LA OTRA TABLA
                    // $destination->$nameAttr[] ="HAY QUE HACER UN GET WHERE DE LOS FILTROS PARA OBTENER EL ARRAY COMPLETO Y VALIDAR SI EXISTE EL ID_ EN LA OTRA TABLA";
                    // break;
                    if (
                        !$this->config->getArraySubModels ||
                        !property_exists($sourceObject, $nameAttr) ||
                        (count($sourceObject->$nameAttr) === 0)
                    ) break;

                    if (!$this->config->canAddRecursive()) break; // CASO ESPECIAL PORQUE EN CASO QUE SEA TRUE SE LE AGREGAR A UN NUMERO

                    $arrayModelType = ucfirst($nameAttr);
                    foreach ($sourceObject->$nameAttr as $relationObject)
                    {
                        $destination->$nameAttr[] = $this->_convertObject($arrayModelType, $relationObject, true);
                    }

                    $this->config->subRecursive();

                    break;

                case 'object':

                    if (
                        !$this->config->getRelations ||
                        !defined(get_class($valueAttr) . '::config')
                    ) break;

                    if (!$this->config->canAddRecursive()) break; // CASO ESPECIAL PORQUE EN CASO QUE SEA TRUE SE LE AGREGAR A UN NUMERO

                    $idSourceObject = 'id_' . $nameAttr;
                    //$objectInCache = $this->Cache->getObjectIsExists(get_class($valueAttr), $sourceObject->$idSourceObject);
                    //if ( $objectInCache === false ) {
                    $valueAttr->setId($sourceObject->$idSourceObject);
                    $objectInCache = $valueAttr->obtain();
                    //$this->Cache->addObject($objectInCache);
                    //}
                    $destination->$nameAttr = $objectInCache;

                    $this->config->subRecursive();

                    break;

                default:
                    if (property_exists($sourceObject, $nameAttr) && !is_null($sourceObject->$nameAttr))
                    {
                        $setMethod = 'set' . ucfirst($nameAttr);
                        $destination->$setMethod($sourceObject->$nameAttr);
                    }
                    break;

            }

        }

        self::_cleanObject($destination);

        return $destination;

    }


    /**
     *
     * REALIZA EL GUARDADO DE UNA CONFIGURACION DE TIPO N TO N EN UN NUEVO GUARDADO.
     *
     * @param array $objectsToSave
     * @param object $objectInserted
     *
     * @return void
     */
    private function _savesNToN (array $objectsToSave, object &$objectInserted): void
    {

        if (count($objectsToSave) === 0) {
            return;
        }

        if (!Config::hasConfig($objectInserted, 'nTon')) {
            return;
        }

        $config = $objectInserted::config['nTon'];
        $attr = $objectsToSave[0]::table;

        $model = array_search($attr, $config);

        $objectModel = model(Config::modelsFolder . $model);

        $objectInsertedSetMethod = 'set' . ucfirst($objectInserted::table);
        $setMethod = 'set' . ucfirst($attr);

        $countObjectsToSave = count($objectsToSave);

        for ($i = 0; $i < $countObjectsToSave; $i++) {

            if (is_null($objectsToSave[$i]->getId()) || $objectsToSave[$i]->getId() === 0) $objectsToSave[$i]->keep();

            $objectModel->$setMethod($objectsToSave[$i]);
            $objectModel->$objectInsertedSetMethod($objectInserted);
            $objectModel->keep();

        }

    }


    /**
     *
     * REALIZA EL GUARDADO DE UNA CONFIGURACION DE TIPO UNO A N EN UN NUEVO GUARDADO.
     *
     * @param array $objectsToSave
     * @param string $name_attribute
     * @param object $objectInserted
     *
     * @return void
     * @throws MethodNotExists
     */
    private function _savesOneToMuch (array $objectsToSave, string $name_attribute, object $objectInserted): void
    {

        $objectInsertedGetMethod = 'get' . ucfirst($objectInserted::table);
        $countObjectsToSave = count($objectsToSave);

        for ($index = 0; $index < $countObjectsToSave; $index++) {
            if (method_exists($objectsToSave[$index], $objectInsertedGetMethod)) {
                $countObjectsToSave[$index]->$objectInsertedGetMethod()->setId($objectInserted->getId()); // ERROR SI SE ASIGNA UN ARREGLO DE UNO A N Y EL LA OTRA CLASE NO EXSITE LA RELACION DE UNO A UNO CON LA CLASE QUE LE LLAMA
                $countObjectsToSave[$index]->save();
            } else {
                throw new MethodNotExists(get_class($countObjectsToSave[$index]), $objectInsertedGetMethod);
            }
        }

    }


    /**
     *
     */
    private function _innitWhereConnection (): void
    {
        $this->queryBuldier = new QueryBuldier($this->builder);
    }


    /**
     * @param CICrud $objectInner
     * @param string $tableParent
     */
    private function _setInnerJoin (CICrud $objectInner, string $tableParent): void
    {

        foreach ($objectInner as $attribute => $valueAttribute) {

            if (!(bool)$valueAttribute || is_array($valueAttribute)) {
                continue;
            }

            if (is_object($valueAttribute)) {

                if (!self::_validateAttributesObject($valueAttribute) ||
                    !self::_isValidObject($valueAttribute)
                ) {
                    continue;
                }

                if ($valueAttribute->getId()) {

                    $this->queryBuldier->setWhere(strtolower($attribute) . '.id', $valueAttribute->getId());

                    $this->_setJoin($objectInner, $tableParent);

                    $this->_setInnerJoin($valueAttribute, $objectInner->table);

                } else {

                    foreach ($valueAttribute as $keyAtt => $valueAtt) {

                        if ($valueAtt) {

                            $this->_setJoin($objectInner, $tableParent);

                            $this->queryBuldier->setWhere($attribute . '.' . $keyAtt, $valueAtt);

                        }

                    }

                }

            } else {

                if ($objectInner->table !== $tableParent) { // VALIDAMOS QUE EL OBJETO NO TENGA UN ATRIBUTO DE SI MISMO

                    $this->_setJoin($objectInner, $tableParent);

                    $this->queryBuldier->setWhere($objectInner->table . '.' . $attribute, $valueAttribute);

                } else {

                    $this->queryBuldier->setWhere('id_' . $objectInner->table, $valueAttribute);

                }

            }

        }

    }


    /**
     * @param object $objectInner
     * @param string $tableParent
     */
    private function _setJoin (object $objectInner, string $tableParent): void
    {

        if ($objectInner->table === $tableParent) {
            return;
        }

        $this->queryBuldier->setInnerJoin($objectInner->table, strtolower($tableParent) . '.id_' . $objectInner->table . " = " . $objectInner->table . ".id");

    }


    /**
     * PARA CARGAR DATOS DE TIPO N TO N OR UNO A N
     * @param    $object (object) El objeto entero a filtrar
     * @param    $key (string) El atributo relacional el cual se toma como referencia
     * @return    void
     */
    private function _setDefinedRelation (object &$object, string $key): void
    {

        $hasNToNConfig = Config::hasConfig($object, 'nTon');
        $nToNConfig = [];

        if ($hasNToNConfig) $nToNConfig = $object::config['nTon'];

        $getMethod = 'get' . ucwords($key); // METODO GET DEL ATRIBUTO RELACIONAL
        $objectsToFilter = $object->$getMethod(); // OBTENEMOS EL ARREGLO QUE TIENE LOS OBJETOS A FILTRAR
        $objectsToFilterCount = count($objectsToFilter);

        if ($hasNToNConfig && in_array($key, $nToNConfig)) { // SI ENTRA ES PORQUE ENCONTRO UN ATRIBUTO QUE CORRESPONDE A UNA RELACION N A N

            $model = array_keys($nToNConfig, $key);
            $model = $model[0];

            $model = model(Config::modelsFolder . $model);

            $tableToJoin = $model::table; // TABLA A LA CUAL SE DEBE INTRESAR A BUSCAR LA RELACION

            for ($i = 0; $i < $objectsToFilterCount; $i++) {

                foreach ($objectsToFilter[$i] as $attrKey => $attrValue) {

                    if (!is_array($attrValue) && $attrValue !== NULL) { // SI ENCONTRAMOS UN ATRIBUTO CON UN VALOR DEFINIDO

                        $this->queryBuldier->setInnerJoin($tableToJoin, strtolower($this->table) . '.id = ' . $tableToJoin . '.id_' . $this->table);
                        $this->queryBuldier->setInnerJoin($key, $tableToJoin . '.id_' . $key . ' = ' . $key . '.id');
                        $this->queryBuldier->setWhere($key . '.' . $attrKey, $attrValue);

                    }

                }

            }

        } else { // EN CASO CONTRARIO ES UNA RELACION UNO A N

            for ($i = 0; $i < $objectsToFilterCount; $i++) {

                foreach ($objectsToFilter[$i] as $attrKey => $attrValue) {

                    if (!is_array($attrValue) && !is_object($attrValue) && $attrValue !== NULL) { // SI ENCONTRAMOS UN ATRIBUTO CON UN VALOR DEFINIDO

                        $this->queryBuldier->setInnerJoin($key, strtolower($this->table) . '.id = ' . $objectsToFilter[$i]::table . '.id_' . $this->table);
                        $this->queryBuldier->setWhere($key . '.' . $attrKey, $attrValue);

                    }

                }

            }

        }

    }


    /**
     * @param string $nameTableParent
     * @param int $idParent
     * @param array $objectsInPut
     * @param string $modelNToN
     * @param string $attrToUpdate
     * @return    boolean         Estado de la solicitud
     */
    private function _updateNToN (string $nameTableParent, int $idParent, array $objectsInPut, string $modelNToN, string $attrToUpdate): bool
    {

        $modelNToN = model(Config::modelsFolder . $modelNToN);
        $tableNToN = $modelNToN::table;

        $idsInDb = []; // ARREGLO EN DONDE SE GUARDAN LOS ID RELACIONALES QUE ESTAN ACUALMENTE EN DB
        $idsInPut = []; // ARREGLO EN DONDE SE RELACIONALES QUE SON ENVIADOS EN EL PUT

        $objectsInDb = $this->_getRelationObjects($tableNToN, $nameTableParent, $idParent);

        if (count($objectsInPut) === 0 && count($objectsInDb) > 0) { // CASO EN EL QUE SE QUIEREN BORRAR TODAS LAS RELACIONES QUE ESTAN EN DB

            foreach ($objectsInDb as $objectInDb) {

                $attrToGet = 'id_' . $attrToUpdate;

                if (!$this->_deleteOnUpdate($tableNToN, $objectInDb->$attrToGet, $attrToUpdate, $idParent, $nameTableParent)) {
                    return FALSE;
                }

            }

            return TRUE;

        }

        foreach ($objectsInPut as $objectInPut) { // RECORREMOS LOS OBJETOS EN NOS ENVIARON EN EL PUT Y OBTENEMOS SUS IDS

            $id = $objectInPut->getId();
            $idsInPut[$id] = $id;

            $propertyToSearch = 'id_' . $objectInPut::table;

            foreach ($objectsInDb as $objectInDb) { // RECORREMOS LOS OBJETOS QUE TENEMOS EN BD

                $propertyValue = $objectInDb->$propertyToSearch;
                $idsInDb[$propertyValue] = $propertyValue;

            }

        }

        foreach ($idsInPut as $idInPut) { // RECORREMOS SOLO LOS ID QUE NOS ENVIARON

            if (!in_array($idInPut, $idsInDb)) { // SI EL ID QUE NOS ENVIARON NO ESTA EN LA BD, SIGNIFICA QUE HAY QUE AGERGARLO

                if (!$this->_insertOnUpdate($tableNToN, $idInPut, $attrToUpdate, $idParent, $nameTableParent)) {
                    return FALSE;
                }

            }

        }

        foreach ($idsInDb as $idInDb) { // RECORREMOS LOS IDS QUE TENEMOS EN BASE DE DATOS

            if (!in_array($idInDb, $idsInPut)) { // SI EL ID QUE TENEMOS EN BD NO ESTA EN EL PUT, SE DEBE ELIMINAR

                if (!$this->_deleteOnUpdate($tableNToN, $idInDb, $attrToUpdate, $idParent, $nameTableParent)) {
                    return FALSE;
                }

            }

        }

        return TRUE;

    }


    /**
     * @param string $table Nombre de la tabla relacional n a n
     * @param string $attr Nombre del atributo relaciona
     * @param int $idFilter Id relacional que se va a buscar
     * @return    array         Objetos que estan en la base de datos sin formato CICrud
     */
    private function _getRelationObjects (string $table, string $attr, int $idFilter): array
    {

        $newConnection = Database::connect();
        $newDBBuilder = $newConnection->table($table);
        $newDBBuilder->where('id_' . $attr, $idFilter);

        return $newDBBuilder->get()->getResult(); // TODO USE TRY CATCH?

    }


    /**
     * @param string $table
     * @param int $idToDelete
     * @param string $attrToDelete
     * @param int $idTableParent
     * @param string $tableParent
     * @return bool
     */
    private function _deleteOnUpdate (string $table, int $idToDelete, string $attrToDelete, int $idTableParent, string $tableParent): bool
    {

        $dbConnection = Database::connect();
        $newDBBuilder = $dbConnection->table($table);

        $newDBBuilder->where('id_' . $attrToDelete, $idToDelete);
        $newDBBuilder->where('id_' . $tableParent, $idTableParent);

        if ($newDBBuilder->delete()) { // TODO USE TRY CATCH
            return TRUE;
        } else {
            return FALSE;
        }

    }


    /**
     * @param string $table
     * @param int $idToInsert
     * @param string $attrToInsert
     * @param int $idTableParent
     * @param string $tableParent
     * @return bool
     */
    private function _insertOnUpdate (string $table, int $idToInsert, string $attrToInsert, int $idTableParent, string $tableParent): bool
    {

        $dbConnection = Database::connect();
        $newDBBuilder = $dbConnection->table($table);

        $newDBBuilder->set('id_' . $attrToInsert, $idToInsert);
        $newDBBuilder->set('id_' . $tableParent, $idTableParent);

        if ($newDBBuilder->insert()) { // TODO USE TRY CATCH
            return TRUE;
        } else {
            return FALSE;
        }

    }


    /**
     * @param object $object
     * @return bool
     */
    private static function _validateAttributesObject (object $object): bool
    {
        foreach ($object as $value) {
            if (is_object($value)) { // EN CASO DE UN OBJETO NO SE PROCESA Y SIGUE
                continue;
            }
            if ($value) {
                return TRUE;
            }
        }
        return FALSE;
    }


    /**
     * @param object $value
     * @return bool
     */
    private static function _isValidObject (object $value): bool
    {
        foreach (self::attrScape as $scape) {
            if (strpos(get_class($value), $scape) !== false || $value instanceof $scape) {
                return false;
            }
        }
        return true;
    }


    /**
     * @param object $object
     */
    private static function _cleanObject (object $object): void
    {
        foreach ($object as $key => $value) {
            if (!is_object($value)) continue;
            foreach (self::attrScape as $scape) {
                if (strpos(get_class($value), $scape) !== false || $value instanceof $scape) {
                    unset($object->$key);
                }
            }
        }
    }

    /**
     * -------------------------------------------------------------------------
     * FUNCIONES PARA HEREDAR
     * -------------------------------------------------------------------------
     */

    public function getTable(): string {
        return $this->table;
    }

    public abstract function getId(): int;
    public abstract function setId(int $id): void;

    public abstract function obtain ();

    public abstract function getAll (int $limit_count = 10, int $start_in = 0, string $orderBy = "");

    public abstract function search (int $limit_count = 10, int $start_in = 0, string $orderBy = "");

    public abstract function keep ();

    public abstract function modify ();

    public abstract function remove ();

}

