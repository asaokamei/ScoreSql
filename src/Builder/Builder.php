<?php
namespace WScore\SqlBuilder\Builder;

use WScore\SqlBuilder\Sql\Join;
use WScore\SqlBuilder\Sql\Sql;

class Builder
{
    /**
     * @var Bind
     */
    protected $bind;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var string
     */
    protected $quoteChar = '"';

    /**
     * @var Sql
     */
    protected $query;

    protected $select = [
        'flags',
        'column',
        'from',
        'tableAlias',
        'join',
        'where',
        'groupBy',
        'having',
        'orderBy',
        'limit',
        'offset',
        'forUpdate',
    ];

    protected $count = [
        'flags',
        'countColumn',
        'from',
        'tableAlias',
        'join',
        'where',
        'groupBy',
        'having',
    ];

    protected $insert = [
        'table',
        'insertCol',
        'insertVal'
    ];

    protected $update = [
        'table',
        'updateSet',
        'where',
    ];

    protected $delete = [
        'table',
        'where',
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
        $this->quote->setQuote( $this->quoteChar );
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
     * @param \WScore\SqlBuilder\Sql\Sql $query
     */
    protected function setQuery( $query )
    {
        $this->query = $query;
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getMagicQuery( $key )
    {
        if( $key == 'getAliasOrTable' ) {
            return $this->query->magicGet('tableAlias') ?: $this->query->magicGet('table');
        }
        return $this->query->magicGet( $key );
    }

    /**
     * @param string|array $name
     * @param string $alias
     * @return string
     */
    protected function quote( $name, $alias=null )
    {
        if( !$this->quote ) return $name;
        if( is_array( $name ) ) return $this->quote->map( $name, $alias );
        return $this->quote->quote( $name, $alias );
    }

    // +----------------------------------------------------------------------+
    //  convert to SQL statements.
    // +----------------------------------------------------------------------+
    /**
     * @param Sql $query
     * @return string
     */
    public function toSelect( $query )
    {
        $this->setQuery( $query );
        $sql = 'SELECT' . $this->buildByList( $this->select );
        return $sql;
    }

    /**
     * @param $query
     * @return string
     */
    public function toCount( $query )
    {
        $this->setQuery( $query );
        $sql = 'SELECT' . $this->buildByList( $this->count );
        return $sql;
    }
    
    /**
     * @param \WScore\SqlBuilder\Sql\Sql $query
     * @return string
     */
    public function toInsert( $query )
    {
        $this->setQuery( $query );
        $sql = 'INSERT INTO' . $this->buildByList( $this->insert );
        return $sql;
    }

    /**
     * @param \WScore\SqlBuilder\Sql\Sql $query
     * @return string
     */
    public function toUpdate( $query )
    {
        $this->setQuery( $query );
        $sql = 'UPDATE' . $this->buildByList( $this->update );
        return $sql;
    }

    /**
     * @param \WScore\SqlBuilder\Sql\Sql $query
     * @return string
     */
    public function toDelete( $query )
    {
        $this->setQuery( $query );
        $sql = 'DELETE FROM' . $this->buildByList( $this->delete );
        return $sql;
    }

    // +----------------------------------------------------------------------+
    //  builders
    // +----------------------------------------------------------------------+
    /**
     * @param $list
     * @return string
     */
    protected function buildByList( $list )
    {
        $statement = '';
        foreach ( $list as $item ) {
            $method = 'build' . ucwords( $item );
            if ( $sql = $this->$method() ) {
                $statement .= ' ' . $sql;
            }
        }
        return $statement;
    }

    /**
     * @return string
     */
    protected function buildInsertCol()
    {
        $keys    = array_keys( $this->getMagicQuery('values') );
        $columns = [ ];
        foreach ( $keys as $col ) {
            $columns[ ] = $this->quote( $col );
        }
        return '( ' . implode( ', ', $columns ) . ' )';
    }

    /**
     * @return string
     */
    protected function buildInsertVal()
    {
        $columns = [ ];
        foreach ( $this->getMagicQuery('values') as $col => $val ) {
            $val = $this->bind->prepare( $val, $col );
            if ( is_callable( $val ) ) {
                $columns[ ] = $val();
            } else {
                $columns[ ] = $val;
            }
        }
        return 'VALUES ( ' . implode( ', ', $columns ) . ' )';
    }

    protected function buildUpdateSet()
    {
        $setter = [ ];
        foreach ( $this->getMagicQuery('values') as $col => $val ) {
            $val = $this->bind->prepare( $val, $col );
            if ( is_callable( $val ) ) {
                $val = $val();
            }
            $col       = $this->quote( $col );
            $setter[ ] = $this->quote( $col ) . '=' . $val;
        }
        return 'SET ' . implode( ', ', $setter );
    }

    /**
     * @return string
     */
    protected function buildFlags()
    {
        return $this->getMagicQuery('selFlags') ? implode( ' ', $this->getMagicQuery('selFlags') ) : '';
    }

    /**
     * @return string
     */
    protected function buildTable()
    {
        return $this->quote( $this->getMagicQuery('table') );
    }

    /**
     * @return string
     */
    protected function buildFrom()
    {
        return 'FROM ' . $this->quote( $this->getMagicQuery('table') );
    }

    /**
     * @return string
     */
    protected function buildTableAlias()
    {
        return $this->getMagicQuery('tableAlias') ? $this->quote( $this->getMagicQuery('tableAlias') ) : '';
    }

    /**
     * @return string
     */
    protected function buildJoin()
    {
        if( !$join_list = $this->getMagicQuery('join') ) return '';
        $joined = '';
        foreach( $join_list as $join ) {
            if( is_string( $join ) ) {
                $joined .= $join;
            } elseif( $join instanceof Join ) {
                $joined .= $join->build( $this->bind, $this->quote, $this->getMagicQuery('getAliasOrTable') );
            }
        }
        return $joined;
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function buildColumn()
    {
        if ( !$column_list = $this->getMagicQuery('columns') ) {
            return '*';
        }
        $columns = [ ];
        foreach ( $column_list as $alias => $col ) {
            if( is_callable($col) ) {
                $col = $col();
            } else {
                $col = $this->quote( $col );
            }
            if ( !is_numeric( $alias ) ) {
                $col .= ' AS ' . $this->quote( $alias );
            }
            $columns[ ] = $col;
        }
        return implode( ', ', $columns );
    }

    /**
     * @return string
     */
    protected function buildCountColumn()
    {
        return 'COUNT(*) AS count';
    }

    /**
     * @return string
     */
    protected function buildGroupBy()
    {
        if ( !$group = $this->getMagicQuery('group') ) return '';
        $group = $this->quote( $group );
        return $group ? 'GROUP BY ' . implode( ', ', $group ) : '';
    }

    /**
     * @return string
     */
    protected function buildOrderBy()
    {
        if ( !$orders = $this->getMagicQuery('order') ) return '';
        $sql = [ ];
        foreach ( $orders as $order ) {
            $sql[ ] = $this->quote( $order[ 0 ], $this->getMagicQuery('tableAlias') ) . " " . $order[ 1 ];
        }
        return 'ORDER BY ' . implode( ', ', $sql );
    }

    /**
     * @return string
     */
    protected function buildLimit()
    {
        if( !$limit = $this->getMagicQuery('limit') ) return '';
        if ( is_numeric( $limit ) && $limit > 0 ) {
            $limit = $this->bind->prepare( $limit );
            return "LIMIT " . $limit;
        }
        return '';
    }

    /**
     * @return string
     */
    protected function buildOffset()
    {
        if( !$offset = $this->getMagicQuery('offset') ) return '';
        if( is_numeric( $offset ) && $offset > 0 ) {
            $offset = $this->bind->prepare( $offset );
            return "OFFSET " . $offset;
        }
        return '';
    }

    /**
     * @return string
     */
    protected function buildForUpdate()
    {
        if ( $this->getMagicQuery('forUpdate') ) {
            return 'FOR UPDATE';
        }
        return '';
    }
    // +----------------------------------------------------------------------+
    //  builders for where clause.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    protected function buildWhere()
    {
        if( !$criteria = $this->getMagicQuery('where') ) {
            return '';
        }
        $sql  = $criteria->build( $this->bind, $this->quote, $this->getMagicQuery('tableAlias') );
        return $sql ? 'WHERE ' . $sql : '';
    }

    /**
     * @return string
     */
    protected function buildHaving()
    {
        if ( !$criteria = $this->getMagicQuery('having') ) return '';
        $sql  = $criteria->build( $this->bind, $this->quote );
        return $sql ? 'HAVING ' . $sql : '';
    }
    // +----------------------------------------------------------------------+
}