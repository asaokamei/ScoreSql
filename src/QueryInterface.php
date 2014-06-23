<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2014/06/23
 * Time: 16:40
 */
namespace WScore\SqlBuilder;

interface QueryInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function insert( $data = array() );

    /**
     * @param null|int $limit
     * @return string
     */
    public function select( $limit = null );

    /**
     * @return string
     */
    public function delete();

    /**
     * @param array $data
     * @return string
     */
    public function update( $data = array() );
}