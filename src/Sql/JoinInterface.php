<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/06/30
 * Time: 16:11
 */

namespace WScore\ScoreSql\Sql;

interface JoinInterface
{
    /**
     * @param string $table
     * @param string $alias
     * @return JoinInterface
     */
    public static function table($table, $alias);

    /**
     * @return JoinInterface
     */
    public function right();

    /**
     * @return JoinInterface
     */
    public function left();

    /**
     * @param Where|string $criteria
     * @return JoinInterface
     */
    public function on($criteria);

    /**
     * @param string $type
     * @return JoinInterface
     */
    public function by($type);

    /**
     * @param string $key
     * @return JoinInterface
     */
    public function using($key);

    /**
     * for setting parent query's table or alias name.
     * will be used in Sql::join method.
     *
     * @param string $queryTable
     * @return JoinInterface
     */
    public function setQueryTable($queryTable);
}