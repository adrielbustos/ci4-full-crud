<?php

namespace App\Models\CICrud;

class ModelConfig {

    /**
    *
    */
    private array $nTon = [];

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
    * 
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
    public function addNtoN(string $nToNModel, string $property) {
        $this->nTon[$nToNModel] = $property;
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