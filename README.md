# ci4-full-crud Version Alpha 0.1
The most powerfull CRUD for you codeiginer 4. You can create a automatic CRUD only wiriting PHP classes.

## Usage
All you need to do is create a simple class in PHP, with all properties and their types, for example in PHP 8

```<?php
namespace App\Models;

use App\Models\CICrud\CICrud;

class UserModel extends CICrud
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $password = null;
    public bool $active = false; // this can be use for set a default value

    public function __construct()
    {
        $this->table = "user"; // The table name in the Database
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
}
```

## Methods

For now is required add this methods in the class for work

```
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

```

## License
[MIT](https://choosealicense.com/licenses/mit/)
