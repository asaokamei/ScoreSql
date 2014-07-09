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
     * @param string $type
     * @return $this
     */
    public function sqlType( $type )
    {
        $this->sqlType = strtolower($type);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $this->setBuilderByType();
        $sql = $this->builder->toSql( $this );
        if( $this->sqlType != 'count' ) {
            $this->reset();
        }
        return $sql;
    }

    /**
     * @return string
     */
    public function toSelect()
    {
        return $this->sqlType( 'select' );
    }

    /**
     * @return $this
     */
    public function toCount()
    {
        return $this->sqlType( 'count' );
    }

    /**
     * @return $this
     */
    public function toInsert()
    {
        return $this->sqlType( 'insert' );
    }

    /**
     * @return $this
     */
    public function toUpdate()
    {
        return $this->sqlType( 'update' );
    }

    /**
     * @return $this
     */
    public function toDelete()
    {
        return $this->sqlType( 'delete' );
    }
}
