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
            ->filter()
            ->pKey->eq('1')
            ->orBracket()
            ->name->startWith('AB')->gender->eq('F')
            ->closeBracket()
            ->end()
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

        $query = Factory::query('mysql')->table('myTable');
        $query->test = 'tested';
        $query->more = 'done';
        $sql = $query->insert();
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
    }

    /**
     * @test
     */
    function update_builds_update_statement()
    {
        $sql = Factory::query()->table( 'myTable' )
            ->filter()
                ->pKey->in( '1', '2' )
            ->end()
            ->update(['test'=>'tested', 'more'=>'done']);
        ;
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=:db_prep_2 ' .
            'WHERE "pKey" IN ( :db_prep_3, :db_prep_4 )',
            $sql );

        $query = Factory::query()->table('myTable');
        $query->test = 'tested';
        $query->more = 'done';
        $sql = $query
            ->filter()
            ->pKey->in( '1', '2' )
            ->end()
            ->update();
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=:db_prep_2 ' .
            'WHERE "pKey" IN ( :db_prep_3, :db_prep_4 )',
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

    /**
     * in README.md
     *
     * @test
     */
    function cool()
    {
        $sql = Factory::query('test')->table('table')
            ->filter()
                ->openBracket()
                    ->gender->is('F')->or()->status->is('1')
                ->closeBracket()
                ->openBracket()
                    ->gender->is('M')->or()->status->is('2')
                ->closeBracket()
            ->end()
            ->order( 'id' )
            ->select(5);
        $this->assertEquals(
            'SELECT * FROM "table" WHERE ' .
            '( "gender" = :db_prep_1 OR "status" = :db_prep_2 ) AND ' .
            '( "gender" = :db_prep_3 OR "status" = :db_prep_4 ) ' .
            'ORDER BY "id" ASC LIMIT :db_prep_5',
            $sql );
    }
}
