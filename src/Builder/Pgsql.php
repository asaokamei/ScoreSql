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
    protected function buildReturning()
    {
        return $this->getMagicQuery('returning') ? 'RETURNING ' . $this->getMagicQuery('returning') : '';
    }

}