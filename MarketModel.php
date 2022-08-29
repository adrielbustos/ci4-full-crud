<?php

namespace App\Models;

use App\Models\CICrud\CICrud;

class MarketModel extends CICrud
{
    public ?int $id = null;
    public ?string $name = null;

    public function __construct()
    {
        $this->table = "market";
        parent::__construct();
        $this->modelConfig->addUnique("name");
    }
    
    public function getId(): int
    {
        return (int)$this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    /*
    | -------------------------------------------------------------------------
    | GET`S
    | -------------------------------------------------------------------------
    */

    public function obtain(): MarketModel
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
