<?php

namespace App\Models\CICrud;
use Error;

class ModelConfig {

    /**
    *
    */
    private array $nTon = [];
    private bool $findNtoN = true;

    /**
    *
    */
    private array $canNull = [];

    /**
    *
    */
    private array $unique = [];

    /**
    *
    */
    private array $index = [];

    /**
     * Combinated Index for unique in MySql DB
     */
    private array $combinatedIndex = [];

    /**
    *
    */
    private array $parentsObjects = [];

    /**
    *
    */
    private array $default = [];

    /**
    *
    */
    private array $isDate = [];

    public function __construct() {}

    /**
    * @return array<string>
    */
    public function getNtoN(): array {
        return $this->nTon;
    }

    /**
    * 
    */
    public function getCanNull() : array {
        return $this->canNull;
    }

    /**
    * 
    */
    public function getUnique(): array {
        return $this->unique;
    }

    /**
    * 
    */
    public function getIndex(): array {
        return $this->index;
    }

    /**
     * @var array<string> $params
     */
    public function addCombinatedIndex(array $params): void {
        foreach ($params as $value) {
            if (gettype($value) != "string") {
                throw new Error("$value should be a string");
            }
        }
        $this->combinatedIndex[] = $params;
    }

    /**
     * 
     */
    public function getCombindatedIndex(): array {
        return $this->combinatedIndex;
    }

    /**
    * @return array<string>
    */
    public function getParentsObjects(): array {
        return $this->parentsObjects;
    }

    /**
    * 
    */
    public function getDefault(): array {
        return $this->default;
    }

    /**
    * 
    */
    public function getIsDate(): array {
        return $this->isDate;
    }

    /**
    *
    */
    public function addNtoN(string $nToNModel, string $property, bool $find = true) {
        $this->nTon[$nToNModel] = $property;
        $this->findNtoN = $find;
    }

    public function getFindNtoN(): bool {
        return $this->findNtoN;
    }

    /**
    *
    */
    public function addCanNull(string $canNull) {
        $this->canNull[] = $canNull;
    }

    /**
    *
    */
    public function addUnique(string $unique) {
        $this->unique[] = $unique;
    }

    /**
    *
    */
    public function addIndex(string $index) {
        $this->index[] = $index;
    }

    /**
    *
    */
    public function addParentsObjects(string $parentsObjects) {
        $this->parentsObjects[] = $parentsObjects;
    }

    /**
    *
    */
    public function addDefault(string $default) {
        $this->default[] = $default;
    }

    /**
    *
    */
    public function addIsDate(string $isDate) {
        $this->isDate[] = $isDate;
    }


}