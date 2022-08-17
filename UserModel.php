<?php

namespace App\Models;

use App\Models\CICrud\CICrud;

class UserModel extends CICrud
{
    // const config = ['nTon' => [], 'canNull' => [], 'unique' => ['email',], 'index' => [], 'parentsObjects' => [], 'default' => [], 'isDate' => []];
    public ?int $id = null;
    public ?string $email = null;
    public ?string $password = null;
    public bool $active = false;

    public function __construct()
    {
        $this->table = "user";
        parent::__construct();
        $this->modelConfig->addUnique("email");
    }
    public function getId(): int
    {
        return (int)$this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getEmail(): string
    {
        return (string)$this->email;
    }
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
    public function getPassword(): string
    {
        return (string)$this->password;
    }
    public function setPassword(string $password)
    {
        $this->password = $password;
    }
    public function getActive(): bool
    {
        return (bool)$this->active;
    }
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /*
    | -------------------------------------------------------------------------
    | GET`S
    | -------------------------------------------------------------------------
    */

    public function obtain(): UserModel
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
