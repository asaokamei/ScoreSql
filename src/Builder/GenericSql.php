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
     * @var Builder
     */
    protected $builder = null;

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
     * @param Sql $query
     */
    public function __construct( $bind, $quote, $builder, $query )
    {
        $this->quote = $quote;
        $this->quote->setQuote( $this->quoteChar );
        $this->bind  = $bind;
        $this->builder = $builder;
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
        /** @noinspection PhpUnusedParameterInspection */
        return '( ' . $this->formatList(
            $this->getMagicQuery('values'),
            function( $col, $val ) {
                return $this->quote( $col );
            }
        ) . ' )';
    }

    /**
     * @return string
     */
    protected function buildInsertVal()
    {
        return 'VALUES ( ' . $this->formatList(
            $this->getMagicQuery('values'),
            function( $col, $val ) {
                return $this->evaluate( $val ) ?: $this->prepare( $val, $col );
            }
        ) . ' )';
    }

    /**
     * @return string
     */
    protected function buildUpdateSet()
    {
        return 'SET ' . $this->formatList(
            $this->getMagicQuery('values'),
            function( $col, $val ) {
                $val = $this->evaluate( $val ) ?: $this->prepare( $val, $col );
                $col = $this->quote( $col );
                return $this->quote( $col ) . '=' . $val;
            }
        );
    }

    /**
     * @param array    $list
     * @param \Closure $callable
     * @param string   $sep
     * @return string
     */
    protected function formatList( $list, $callable, $sep=', ' )
    {
        foreach( $list as $col => $val ) {
            $list[$col] = $callable( $col, $val );
        }
        return implode( $sep, $list );
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
        /** @noinspection PhpUnusedParameterInspection */
        return $this->formatList(
            $this->getMagicQuery('join'),
            function( $col, $join ) {
                if( $join instanceof Join ) {
                    $join = $join->build( $this->bind, $this->quote );
                }
                return $join;
            },
            ''
        );
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function buildColumn()
    {
        if ( !$column_list = $this->getMagicQuery('columns') ) return '*';
        return $this->formatList(
            $column_list,
            function( $alias, $col ) {
                $col = $this->evaluate( $col ) ?: $this->quote($col);
                if ( !is_numeric( $alias ) ) {
                    $col .= ' AS ' . $this->quote( $alias );
                }
                return $col;
            }
        );
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
        /** @noinspection PhpUnusedParameterInspection */
        return 'ORDER BY ' . $this->formatList(
            $orders,
            function( $col, $order ) {
                return $this->quote( $order[ 0 ], $this->getMagicQuery('tableAlias') ) . " " . $order[ 1 ];
            }
        );
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
            $builder = new Builder( $this->bind, $this->quote );
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
        $criteria = $this->buildCriteria( 'where' );
        return $criteria ? 'WHERE '.$criteria : '';
    }

    /**
     * @return string
     */
    protected function buildHaving()
    {
        $criteria = $this->buildCriteria( 'having' );
        return $criteria ? 'HAVING '.$criteria : '';
    }

    /**
     * @param string $type
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function buildCriteria( $type )
    {
        if( !$criteria = $this->getMagicQuery($type) ) return '';
        if( !$criteria instanceof Where ) {
            throw new \InvalidArgumentException;
        }
        $criteria->setBuilder( $this->builder );
        $sql = $criteria->build( $this->bind, $this->quote, $this->getMagicQuery('tableAlias'), $this->getMagicQuery('tableParent') );
        return $sql;
    }
    // +----------------------------------------------------------------------+
}