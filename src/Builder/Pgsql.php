<?php
namespace WScore\SqlBuilder\Builder;

class Pgsql extends Builder
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

    protected $insert = [
        'table',
        'insertCol',
        'insertVal',
        'returning',
    ];

    protected $update = [
        'table',
        'updateSet',
        'where',
        'returning',
    ];

    protected $delete = [
        'table',
        'where',
        'returning',
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