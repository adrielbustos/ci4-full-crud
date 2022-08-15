<?php

namespace App\Models\CICrud\QueryBuldier;

use CodeIgniter\Database\BaseBuilder;

class WhereConnection
{

    /**
     * @var array
     */
    private array $innerJoin = [];
    /**
     * @var array
     */
    private array $leftJoin = [];
    /**
     * @var array
     */
    private array $where = [];

    /**
     * @var BaseBuilder
     */
    private BaseBuilder $whereBaseBuilder;

    /**
     * @param BaseBuilder $WBB
     */
    public function __construct (BaseBuilder &$WBB)
    {
        $this->whereBaseBuilder = $WBB;
    }

    /**
     * @param string $k
     * @param string $v
     */
    public function setInnerJoin (string $k, string $v): void
    {
        $this->innerJoin[$k] = $v;
    }

    /**
     * @param string $k
     * @param $v
     */
    public function setWhere (string $k, $v): void
    {
        $this->where[$k] = $v;
    }

    /**
     * @param string $k
     * @param string $v
     */
    public function setLeftJoin (string $k, string $v): void
    {
        $this->leftJoin[$k] = $v;
    }

    /**
     *
     */
    public function setRelations (): void
    {

        if ($this->innerJoin) {
            foreach ($this->innerJoin as $k => $v) {
                $this->whereBaseBuilder->join($k, $v);
            }
        }

        if ($this->leftJoin) {
            foreach ($this->leftJoin as $k => $v) {
                $this->whereBaseBuilder->join($k, $v, 'left');
            }
        }

        if ($this->where) {

            foreach ($this->where as $k => $v) {

                switch (gettype($v)) {

                    case 'string':
                        if ($v != '') { // TODO TENER EN CUENTA LOS CAMPOS VACIOS?
                            $this->whereBaseBuilder->like($k, $v);
                        }
                        break;

                    default:
                        $this->whereBaseBuilder->where($k, $v);
                        break;
                }

            }

        }

        $this->innerJoin = [];
        $this->where = [];
        $this->leftJoin = [];

    }

}
