<?php
namespace WScore\ScoreSql\Builder;

use WScore\ScoreSql\Sql\Join;
use WScore\ScoreSql\Sql\Sql;
use WScore\ScoreSql\Sql\SqlInterface;
use WScore\ScoreSql\Sql\Where;

class Builder
{
    /**
     * @var Bind
     */
    protected $bind = null;

    /**
     * @var Quote
     */
    protected $quote = null;

    /**
     * @var string
     */
    protected $quoteChar = '"';

    /**
     * @var Sql
     */
    protected $query;

    /**
     * @var GenericSql
     */
    protected $builder;

    protected $sqlHeaders = [
        'select' => 'SELECT',
        'count'  => 'SELECT',
        'insert' => 'INSERT INTO',
        'update' => 'UPDATE',
        'delete' => 'DELETE FROM',
    ];

    // +----------------------------------------------------------------------+
    //  construction
    // +----------------------------------------------------------------------+
    /**
     * @param Bind  $bind
     * @param Quote $quote
     */
    public function __construct( $bind, $quote )
    {
        $this->quote = $quote;
        $this->bind  = $bind;
    }

    /**
     * @return Bind
     */
    public function getBind()
    {
        return $this->bind;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Sql $query
     */
    protected function setQuery( $query )
    {
        $this->query = $query;
        $dbType  = $query->magicGet('dbType') ?: 'GenericSql';
        $dbType  = ucwords( $dbType );
        /** @var GenericSql $builder */
        $class = '\WScore\ScoreSql\Builder\\'.$dbType;
        $this->builder = new $class( $this->bind, $this->quote, $this );
        $this->builder->setQuery($this->query);
    }

    // +----------------------------------------------------------------------+
    //  convert to SQL statements.
    // +----------------------------------------------------------------------+
    /**
     * @param Sql|SqlInterface $query
     * @return string
     */
    public function toSql( $query )
    {
        $type = $query->magicGet( 'sqlType' );
        $method = 'to' . ucwords($type);
        return $this->$method( $query );
    }

    /**
     * @param Sql $query
     * @return string
     */
    public function toSelect( $query )
    {
        $this->setQuery( $query );
        $sql = 'SELECT' . $this->builder->build( 'select' );
        return $sql;
    }

    /**
     * @param $query
     * @return string
     */
    public function toCount( $query )
    {
        $this->setQuery( $query );
        $sql = 'SELECT' . $this->builder->build( 'count' );
        return $sql;
    }
    
    /**
     * @param Sql $query
     * @return string
     */
    public function toInsert( $query )
    {
        $this->setQuery( $query );
        $sql = 'INSERT INTO' . $this->builder->build( 'insert' );
        return $sql;
    }

    /**
     * @param Sql $query
     * @return string
     */
    public function toUpdate( $query )
    {
        $this->setQuery( $query );
        $sql = 'UPDATE' . $this->builder->build( 'update' );
        return $sql;
    }

    /**
     * @param Sql $query
     * @return string
     */
    public function toDelete( $query )
    {
        $this->setQuery( $query );
        $sql = 'DELETE FROM' . $this->builder->build( 'delete' );
        return $sql;
    }

    // +----------------------------------------------------------------------+
}