<?php
namespace WScore\SqlBuilder;

use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Sql\Sql;

class Query extends Sql implements QueryInterface
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
     * @param null|int $limit
     * @return string
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        return $this->builder->toSelect( $this );
    }

    /**
     * @return int
     */
    public function count()
    {
        $origColumn = $this->columns;
        $this->column( false ); // reset columns
        $this->column( $this::raw( 'COUNT(*)'), 'count' );
        $sql = $this->select();
        $this->columns = $origColumn;
        return $sql;
    }

    /**
     * for paginate.
     *
     * $perPage is a default number of rows per page, but
     * does not override the $limit if already set.
     *
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function page( $page, $perPage=20 )
    {
        $page = (int) ( $page > 0 ?: 1 );
        if( !$this->limit ) {
            $this->limit( $perPage );
        }
        $this->offset( $perPage * ($page - 1) );
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $this->setId($id, $column);
        return $this->select();
    }

    /**
     * @param        $id
     * @param string $column
     */
    protected function setId( $id, $column=null )
    {
        if( !$id ) return;
        $column = $column ?: $this->keyName;
        $this->where( $this->$column->eq( $id ) );
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
     * @param int $id
     * @return string
     */
    public function delete( $id=null )
    {
        $this->setId($id);
        return $this->builder->toDelete( $this );
    }

    /**
     * @return array
     */
    public function getBind()
    {
        return $this->builder->getBind()->getBinding();
    }

    /**
     *
     */
    public function reset()
    {
        $this->where     = null;
        $this->join      = [ ];
        $this->columns   = [ ];
        $this->values    = [ ];
        $this->selFlags  = [ ];
        $this->order     = [ ];
        $this->group     = [ ];
        $this->having    = null;
        $this->limit     = null;
        $this->offset    = 0;
        $this->returning = null;
        $this->forUpdate = false;
    }

}
