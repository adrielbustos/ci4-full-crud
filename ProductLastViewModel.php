<?php

namespace App\Models;

use App\Models\CICrud\CICrud;

class ProductLastViewModel extends CICrud
{
    public ?int $id = null;
    public ?ProductModel $product = null;
    public ?UserModel $user = null;

    public function __construct()
    {
        $this->table = "productlastview";
        parent::__construct();
        $this->modelConfig->addCombinatedIndex(["product", "user"]);
        
        $this->product = new ProductModel();
        $this->user = new UserModel();
    }
    
    public function getId(): int
    {
        return (int)$this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    public function getProduct(): ProductModel
    {
        return $this->product;
    }
    
    public function setProduct(ProductModel $product): void
    {
        $this->$product = $product;
    }

    public function getUser(): UserModel
    {
        return $this->user;
    }
    
    public function setUser(UserModel $user): void
    {
        $this->$user = $user;
    }
    
    /*
    | -------------------------------------------------------------------------
    | GET`S
    | -------------------------------------------------------------------------
    */

    public function obtain(): ProductLastViewModel
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
