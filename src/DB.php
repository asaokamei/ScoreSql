<?php
namespace WScore\ScoreSql;

use WScore\ScoreSql\Sql\Join;
use WScore\ScoreSql\Sql\Where;

/**
 * Class Query
 *
 * @package WScore\ScoreSql
 *          
 */
class DB
{
    // +----------------------------------------------------------------------+
    //  manage objects, aka Facade.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @param string $alias
     * @return Join
     */
    public static function join( $table, $alias=null )
    {
        return new Join( $table, $alias );
    }

    /**
     * @param string $column
     * @return Where
     */
    public static function given( $column=null )
    {
        return Where::column($column);
    }
}
