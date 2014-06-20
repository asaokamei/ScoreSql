<?php
namespace WScore\SqlBuilder\Builder;

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
        if ( $this->query->limit && $this->query->offset ) {
            $sql .= ' LIMIT ' . $this->query->offset . ' , ' . $this->query->limit;
        } elseif ( $this->query->limit ) {
            $sql .= ' LIMIT ' . $this->query->limit;
        } elseif ( $this->query->offset ) {
            $sql .= ' OFFSET ' . $this->query->offset;
        }
        return $sql;
    }

}