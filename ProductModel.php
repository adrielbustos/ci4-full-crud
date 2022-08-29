<?php

namespace App\Models;

use App\Models\CICrud\CICrud;

class ProductModel extends CICrud {

    public int $id = 0;
    public ?string $name = null;
    public ?string $code = null;
    public ?string $img = null;
    public ?float $weight = null;
    public ?float $price = null;
    public ?WeightTypeModel $weightType = null;
    public array $productlastview = [];

    public function __construct()
    {
        $this->table = "product";
        parent::__construct();
        $this->modelConfig->addUnique("code");
        $this->modelConfig->addNtoN("ProductLastViewModel", "productlastview", false);

        $this->weightType = new WeightTypeModel();
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getWeighttype(): WeightTypeModel {
        return $this->weightType;
    }

    public function getName() : string { 
        return (string)$this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setCode(string $code): void {
        $this->code = $code;
    }

    /*
    | -------------------------------------------------------------------------
    | GET`S
    | -------------------------------------------------------------------------
    */

    public function obtain(): ProductModel
    {
        return parent::getObject($this);
    }

    public function getAll(int $countLimit = 10, int $startIn = 0, string $orderBy = ""): array
    {
        return parent::getAllObjects($this, $countLimit, $startIn, $orderBy);
    }

    public function search(int $countLimit = 10, int $startIn = 0, string $orderBy = ""): array
    {
        return parent::getObjectWhere($this, $countLimit, $startIn, $orderBy);
    }

    /*
    | -------------------------------------------------------------------------
    | SAVE`S
    | -------------------------------------------------------------------------
    */

    public function keep(): int
    {
        return parent::saveObject($this);
    }

    /*
    | -------------------------------------------------------------------------
    | UPDATE`S
    | -------------------------------------------------------------------------
    */

    public function modify(): bool
    {
        return parent::updateObject($this);
    }

    /*
    | -------------------------------------------------------------------------
    | DELETE`S
    | -------------------------------------------------------------------------
    */

    public function remove(): bool
    {
        return parent::deleteObject($this);
    }

}