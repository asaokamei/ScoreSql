<?php
namespace WScore\SqlBuilder;

use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Sql\Sql;
use WScore\SqlBuilder\Sql\Where;

class Query extends Sql
{
    /**
     * @var Builder
     */
    protected $builder;

    // +----------------------------------------------------------------------+
    /**
     * @param Builder $builder
     */
    public function setBuilder( $builder )
    {
        $this->builder = $builder;
    }

    /**
     * @param $column
     * @return Where
     */
    public function __get( $column )
    {
        $where = new Where();
        return $where->col( $column );
    }

    /**
     * @param null|int $limit
     * @return string
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        return $this->builder->toSelect( $this );
    }

    /**
     * @param array $data
     * @return string
     */
    public function insert( $data=array() )
    {
        if( $data ) $this->value($data);
        return $this->builder->toInsert( $this );
    }

    /**
     * @param array $data
     * @return string
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        return $this->builder->toUpdate( $this );
    }

    /**
     * @return string
     */
    public function delete()
    {
        return $this->builder->toDelete( $this );
    }

    /**
     * @return array
     */
    public function getBind()
    {
        return $this->builder->getBind()->getBinding();
    }
}
