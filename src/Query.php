<?php
namespace WScore\ScoreSql;

use WScore\ScoreSql\Builder\Builder;

/**
 * Class Query
 *
 * @package WScore\ScoreSql
 *
 */
class Query extends Sql\Sql
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $bound = [];

    /**
     * @var string
     */
    protected $dbType;

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
        $builder = $this->builder ?: Factory::buildBuilder( $this->dbType );
        $sql = $builder->toSql( $this );
        $this->bound = $builder->getBind()->getBinding();
        return $sql;
    }

    /**
     * @return array
     */
    public function getBind()
    {
        return $this->bound;
    }

    /**
     * @return Query
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
