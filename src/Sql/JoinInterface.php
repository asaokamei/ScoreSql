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
     * @return $this
     */
    public static function right( $table, $alias );

    /**
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public static function left( $table, $alias );

    /**
     * @param Where|string $criteria
     * @return $this
     */
    public function on( $criteria );

    /**
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public static function table( $table, $alias );

    /**
     * @param string $type
     */
    public function by( $type );

    /**
     * @param string $key
     * @return $this
     */
    public function using( $key );

    /**
     * for setting parent query's table or alias name.
     * will be used in Sql::join method.
     *
     * @param string $queryTable
     * @return $this
     */
    public function setQueryTable( $queryTable );
}