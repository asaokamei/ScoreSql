<?php
namespace WScore\ScoreSql\Builder;

use WScore\ScoreSql\Sql\Join;

class GenericSql extends GenericUtils
{
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
        'tableAlias',
        'insertCol',
        'insertVal'
    ];

    protected $update = [
        'tableAlias',
        'updateSet',
        'where',
    ];

    protected $delete = [
        'table',
        'where',
    ];

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
        return $this->formatInteger('limit');
    }

    /**
     * @return string
     */
    protected function buildOffset()
    {
        return $this->formatInteger('offset');
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
        $criteria = $this->formatCriteria( 'where' );
        return $criteria ? 'WHERE '.$criteria : '';
    }

    /**
     * @return string
     */
    protected function buildHaving()
    {
        $criteria = $this->formatCriteria( 'having' );
        return $criteria ? 'HAVING '.$criteria : '';
    }
    // +----------------------------------------------------------------------+
}