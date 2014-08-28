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
        $limit  = (int) $this->getMagicQuery('limit');
        $offset = (int) $this->getMagicQuery('offset');
        if ( $limit && $offset ) {
            return ' LIMIT ' . $offset . ' , ' . $limit;
        }
        if ( $limit ) {
            return ' LIMIT ' . $limit;
        }
        if ( $offset ) {
            return ' OFFSET ' . $offset;
        }
        return '';
    }

}