<?php
namespace WScore\ScoreSql;

use WScore\ScoreSql\Builder\Builder;
use WScore\ScoreSql\Sql\Sql;
use WScore\ScoreSql\Sql\Where;

/**
 * Class Query
 *
 * @package WScore\ScoreSql
 *          
 */
class Query extends Sql
{
    /**
     * @var Query
     */
    static $query;
    
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $dbType;

    // +----------------------------------------------------------------------+
    //  manage objects, aka Facade.
    // +----------------------------------------------------------------------+
    /**
     * @param string $dbType
     * @return Query
     */
    public static function db( $dbType=null )
    {
        $self = Factory::buildQuery();
        $self->dbType( $dbType );
        static::$query = $self;
        return $self;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return Query
     */
    public static function from( $table, $alias = null )
    {
        $self = Factory::buildQuery();
        $self->table( $table, $alias );
        static::$query = $self;
        return $self;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return Sql|self
     */
    public static function subQuery( $table, $alias = null )
    {
        return static::$query->sub( $table, $alias );
    }

    /**
     * @return array
     */
    public static function bind()
    {
        return static::$query->getBind();
    }

    /**
     * @param string $column
     * @return Where
     */
    public static function given( $column=null )
    {
        return Where::column($column);
    }

    // +----------------------------------------------------------------------+
    //  manage Query.
    // +----------------------------------------------------------------------+
    /**
     * @param string $type
     * @return $this
     */
    public function dbType( $type )
    {
        $this->dbType = $type;
        return $this;
    }
    
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

    // +----------------------------------------------------------------------+
    //  builds SQL statements.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function select()
    {
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
        $this->setBuilderByType();
        $sql = $this->builder->toCount( $this );
        return $sql;
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
     * @return string
     */
    public function insert()
    {
        $this->setBuilderByType();
        $sql = $this->builder->toInsert( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @return string
     */
    public function update()
    {
        $this->setBuilderByType();
        $sql = $this->builder->toUpdate( $this );
        $this->reset();
        return $sql;
    }

    /**
     * @return string
     */
    public function delete()
    {
        $this->setBuilderByType();
        $sql = $this->builder->toDelete( $this );
        $this->reset();
        return $sql;
    }
}
