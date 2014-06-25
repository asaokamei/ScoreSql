<?php
namespace WScore\SqlBuilder;

use InvalidArgumentException;
use PDOStatement;
use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Sql\Sql;

class Query extends Sql implements QueryInterface
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $dbType;

    // +----------------------------------------------------------------------+
    //  manage Query.
    // +----------------------------------------------------------------------+
    /**
     * @param Builder $builder
     */
    public function setBuilder( $builder )
    {
        $this->builder = $builder;
    }

    /**
     * set builder based on dbType,
     * if $this->builder is not set.
     */
    protected function setBuilderByType()
    {
        if( !$this->builder ) {
            $this->builder = Factory::buildBuilder( $this->dbType );
        }
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

    // +----------------------------------------------------------------------+
    //  builds SQL statements.
    // +----------------------------------------------------------------------+
    /**
     * @param null|int $limit
     * @return string
     */
    public function select($limit=null)
    {
        if( $limit ) $this->limit($limit);
        $this->setBuilderByType();
        $sql = $this->builder->toSelect( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @param int    $id
     * @param string $column
     * @return array
     */
    public function load( $id, $column=null )
    {
        $this->setId($id, $column);
        $this->setBuilderByType();
        $sql = $this->builder->toSelect( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @return int
     */
    public function count()
    {
        $origColumn = $this->columns;
        $this->column( false ); // reset columns
        $this->column( $this::raw( 'COUNT(*)'), 'count' );
        $this->setBuilderByType();
        $sql = $this->builder->toSelect( $this );
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
        $this->setBuilderByType();
        $sql = $this->builder->toInsert( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @param array $data
     * @return string
     */
    public function update( $data=array() )
    {
        if( $data ) $this->value($data);
        $this->setBuilderByType();
        $sql = $this->builder->toUpdate( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @param int $id
     * @param string $column
     * @return string
     */
    public function delete( $id=null, $column=null )
    {
        $this->setId($id, $column);
        $this->setBuilderByType();
        $sql = $this->builder->toDelete( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @param $data
     * @throws InvalidArgumentException
     * @return int|PdoStatement
     */
    public function save( $data )
    {
        throw new InvalidArgumentException( 'cannot use save method in Query builder. ' );
    }
}
