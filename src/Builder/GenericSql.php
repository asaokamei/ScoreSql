<?php
namespace WScore\ScoreSql\Builder;

use WScore\ScoreSql\Sql\Join;
use WScore\ScoreSql\Sql\Sql;
use WScore\ScoreSql\Sql\SqlInterface;
use WScore\ScoreSql\Sql\Where;

class GenericSql
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
     * @param Builder $builder
     */
    public function __construct( $bind, $quote, $builder )
    {
        $this->quote = $quote;
        $this->quote->setQuote( $this->quoteChar );
        $this->bind  = $bind;
        $this->builder = $builder;
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
    public function setQuery( $query )
    {
        $this->query = $query;
    }

    /**
     * @param string $key
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
        if( is_array( $name ) ) return $this->quote->map( $name );
        return $this->quote->quote( $name, $alias );
    }

    /**
     * @param string|array $val
     * @param null|int|string  $col     column name. used to find data type
     * @param null|int         $type    data type
     * @return array|string
     */
    protected function prepare( $val, $col, $type=null )
    {
        return $this->bind->prepare( $val, $col, $type );
    }
    // +----------------------------------------------------------------------+
    //  builders
    // +----------------------------------------------------------------------+
    /**
     * @param string $sqlType
     * @return string
     */
    public function build($sqlType)
    {
        $list = $this->$sqlType;
        return $this->buildByList($list);
    }
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
            $columns[ ] = $this->evaluate( $val ) ?:$this->prepare( $val, $col );
        }
        return 'VALUES ( ' . implode( ', ', $columns ) . ' )';
    }

    protected function buildUpdateSet()
    {
        $setter = [ ];
        foreach ( $this->getMagicQuery('values') as $col => $val ) {
            $val = $this->evaluate( $val ) ?: $this->prepare( $val, $col );
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
        $table = $this->getMagicQuery('table');
        $table = $this->evaluate( $table ) ?: $this->quote($table);
        return 'FROM ' . $table;
    }

    /**
     * @return string
     */
    protected function buildTableAlias()
    {
        $alias = $this->getMagicQuery('tableAlias');
        if( $alias ) {
            $alias = 'AS ' . $this->quote( $this->getMagicQuery('tableAlias') );
        }
        return $alias;
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
                $joined .= $join->build( $this->getBind(), $this->getQuote() );
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
            $col = $this->evaluate( $col ) ?: $this->quote($col);
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
        $group = (array) $group;
        $group = $this->quote( $group );
        return 'GROUP BY ' . implode( ', ', $group );
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
        return $this->buildLimitOrOffset('limit');
    }

    /**
     * @return string
     */
    protected function buildOffset()
    {
        return $this->buildLimitOrOffset('offset');
    }

    protected function buildLimitOrOffset($type)
    {
        if( !$integer = $this->getMagicQuery($type) ) return '';
        if( !is_numeric( $integer ) ) return '';
        if( $integer <= 0 ) return '';
        return strtoupper($type) . " " . $integer;
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

    /**
     * @param mixed $string
     * @return string
     */
    protected function evaluate( $string )
    {
        if( is_callable($string) ) {
            return $string();
        } elseif( is_object($string) && $string instanceof SqlInterface ) {
            $builder = new Builder( $this->getBind(), $this->getQuote() );
            return '( '.$builder->toSql( $string ).' )';
        }
        return null;
    }
    // +----------------------------------------------------------------------+
    //  builders for where clause.
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    protected function buildWhere()
    {
        return $this->buildCriteria( 'where' );
    }

    /**
     * @return string
     */
    protected function buildHaving()
    {
        return $this->buildCriteria( 'having' );
    }

    /**
     * @param string $type
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function buildCriteria( $type )
    {
        if( !$criteria = $this->getMagicQuery($type) ) return '';
        if( !$criteria ) return '';
        if( !$criteria instanceof Where ) {
            throw new \InvalidArgumentException;
        }
        $criteria->setBuilder( $this->builder );
        $sql = $criteria->build( $this->getBind(), $this->getQuote(), $this->getMagicQuery('tableAlias'), $this->getMagicQuery('tableParent') );
        return $sql ? strtoupper($type) . ' ' . $sql : '';
    }
    // +----------------------------------------------------------------------+
}