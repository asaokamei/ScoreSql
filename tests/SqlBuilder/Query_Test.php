<?php
namespace tests\Sql;

use WScore\SqlBuilder\Factory;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function select_builds_select_statement()
    {
        $sql = Factory::query( 'pgsql' )->table( 'myTable' )
            ->beginWhere()
            ->pKey->eq('1')
            ->orBlock()
            ->name->startWith('AB')->gender->eq('F')
            ->endBlock()
            ->endWhere()
            ->select();
        ;
        $this->assertEquals(
            'SELECT * FROM "myTable" ' .
            'WHERE "pKey" = :db_prep_1 OR ( "name" LIKE :db_prep_2 AND "gender" = :db_prep_3 )',
            $sql );
    }

    /**
     * @test
     */
    function insert_builds_insert_statement()
    {
        $sql = Factory::query( 'mysql' )->table( 'myTable' )
            ->insert(['test'=>'tested', 'more'=>'done']);
        ;
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
    }

    /**
     * @test
     */
    function update_builds_update_statement()
    {
        $sql = Factory::query( 'mysql' )->table( 'myTable' )
            ->beginWhere()
                ->pKey->in( '1', '2' )
            ->endWhere()
            ->update(['test'=>'tested', 'more'=>'done']);
        ;
        $this->assertEquals(
            'UPDATE `myTable` SET `test`=:db_prep_1, `more`=:db_prep_2 ' .
            'WHERE `pKey` IN ( :db_prep_3, :db_prep_4 )',
            $sql );
    }

    /**
     * @test
     */
    function delete_builds_delete_statement()
    {
        $sql = Factory::query( 'mysql' )->table( 'myTable', 'mt' )->keyName('myKey')
            ->delete('3');
        ;
        $this->assertEquals(
            'DELETE FROM `myTable` WHERE `mt`.`myKey` = :db_prep_1',
            $sql );
    }
}