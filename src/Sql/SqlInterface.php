<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/06/23
 * Time: 16:40
 */
namespace WScore\ScoreSql\Sql;

interface SqlInterface
{
    /**
     * @param $column
     * @return Where
     */
    public function __get( $column );

    /**
     * @param $key
     * @param $value
     */
    public function __set( $key, $value );

    /**
     * @param string $keyName
     * @return $this
     */
    public function keyName( $keyName );

    /**
     * @param string $id
     * @param string $column
     * @return $this
     */
    public function setKey( $id, $column=null );

    /**
     * @param string $group
     * @return $this
     */
    public function group( $group );

    /**
     * @param int $offset
     * @return $this
     */
    public function offset( $offset );

    /**
     * @param Where $having
     * @return $this
     */
    public function having( $having );

    /**
     * @param string|array $name
     * @param string|null $value
     * @return $this
     */
    public function value( $name, $value = null );

    /**
     * @param string $column
     * @param null|string $as
     * @return $this
     */
    public function column( $column, $as = null );

    /**
     * @param string $order
     * @param string $sort
     * @return $this
     */
    public function order( $order, $sort = 'ASC' );

    /**
     * @param $flag
     * @return $this
     */
    public function flag( $flag );

    /**
     * creates SELECT DISTINCT statement.
     * @return $this
     */
    public function distinct();

    /**
     * @param bool $for
     * @return $this
     */
    public function forUpdate( $for = true );

    /**
     * @param Where $where
     * @param string|null $andOr
     * @return $this
     */
    public function where( $where, $andOr = null );

    /**
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function table( $table, $alias = null );


    /**
     * @param Join $join
     * @return $this
     */
    public function join( $join );

    /**
     * @param $where
     * @return $this
     */
    public function whereOr( $where );

    /**
     * ->columns( [ 'col1', 'col2', ...] )
     * or
     * ->columns( 'col1', 'col2', ... );
     *
     * @param array $column
     * @return $this
     */
    public function columns( $column );

    /**
     * @param string $return
     * @return $this
     */
    public function returning( $return );

    /**
     * @param $value
     * @return callable
     */
    public static function raw( $value );

    /**
     * @param int $limit
     * @return $this
     */
    public function limit( $limit );
}