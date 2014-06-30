<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/06/23
 * Time: 16:40
 */
namespace WScore\ScoreSql;

use InvalidArgumentException;
use PDOStatement;

interface QueryInterface
{
    /**
     * builds insert statement.
     *
     * @param array $data
     * @return int
     */
    public function insert( $data = array() );

    /**
     * builds select statement.
     *
     * @param null|int $limit
     * @return string
     */
    public function select( $limit = null );

    /**
     * builds select statement with $id as primary-key,
     * or set $column to use another column to select.
     *
     * @param int    $id
     * @param string $column
     * @return string
     */
    public function load( $id, $column=null );

    /**
     * for paginate.
     *
     * $perPage is a default number of rows per page, but
     * does not override the $limit if already set.
     *
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function page( $page, $perPage=20 );

    /**
     * get the current limit value.
     *
     * @return int
     */
    public function getLimit();

    /**
     * builds delete statement.
     *
     * @param int $id
     * @param string $column
     * @return string
     */
    public function delete( $id=null, $column=null );

    /**
     * builds update statement.
     *
     * @param array $data
     * @return PdoStatement
     */
    public function update( $data = array() );

    /**
     * @param $data
     * @throws InvalidArgumentException
     * @return int|PdoStatement
     */
    public function save( $data );

    /**
     * resets the query state.
     */
    public function reset();
}