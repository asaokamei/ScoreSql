<?php
namespace WScore\ScoreSql\Builder;

class Mysql extends Builder
{
    /**
     * @var string
     */
    protected $quoteChar = '`';

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
        'limitOffset',
        'forUpdate',
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
        'limit',
    ];

    protected $delete = [
        'table',
        'where',
        'limit',
    ];

    /**
     * @return string
     */
    protected function buildLimitOffset()
    {
        $sql = '';
        $limit  = $this->getMagicQuery('limit');
        $offset = $this->getMagicQuery('offset');
        if ( $limit && $offset ) {
            $sql .= ' LIMIT ' . $offset . ' , ' . $limit;
        } elseif ( $limit ) {
            $sql .= ' LIMIT ' . $limit;
        } elseif ( $offset ) {
            $sql .= ' OFFSET ' . $offset;
        }
        return $sql;
    }

}