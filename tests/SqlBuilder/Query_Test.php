<?php
namespace tests\Sql;

use WScore\ScoreSql\Factory;
use WScore\ScoreSql\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function simple_example_of_select()
    {
        $query = Factory::query();
        $sql = $query->table('myTable')
            ->column('col1', 'aliased1')
            ->columns( 'col2', 'col3' )
            ->where(
                $query->status->is('1')
            )
            ->select();
        ;
        $this->assertEquals(
            'SELECT "col1" AS "aliased1", "col2", "col3" FROM "myTable" WHERE "status" = :db_prep_1',
            $sql );
    }

    /**
     * @test
     */
    function simple_example_of_insert()
    {
        $query = Factory::query();
        $sql = $query
            ->table('myTable')
            ->insert( [ 'col1' => 'val1', 'col2'=>'val2' ] );
        ;
        $this->assertEquals(
            'INSERT INTO "myTable" ( "col1", "col2" ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
    }

    /**
     * @test
     */
    function simple_example_of_update()
    {
        $query = Factory::query();
        $sql = $query
            ->table('myTable')
            ->where(
                $query->name->like('bob')->or()->status->eq('1')
            )
            ->update( [
                'date' => $query->raw('NOW()'),
                'col2'=>'val2'
            ] );

        $this->assertEquals(
            'UPDATE "myTable" SET "date"=NOW(), "col2"=:db_prep_1 WHERE "name" LIKE :db_prep_2 OR "status" = :db_prep_3',
            $sql );
    }

    /**
     * @test
     */
    function select_builds_select_statement()
    {
        $sql = Factory::query( 'pgsql' )->table( 'myTable' )
            ->where(
                Where::column('pKey')->eq('1')
                    ->orBracket()
                    ->name->startWith('AB')->gender->eq('F')
                    ->closeBracket()
            )
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
            ->where(
                Where::column('pKey')->in( '1', '2' )
            )
            ->update(['test'=>'tested', 'more'=>'done']);
        ;
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=:db_prep_2 ' .
            'WHERE "pKey" IN ( :db_prep_3, :db_prep_4 )',
            $sql );

        $query = Factory::query()->table('myTable');
        $query->test = 'tested';
        $query->more = $query->raw('NOW()');
        $sql = $query
            ->where(
                Where::column('pKey')->in( '1', '2' )
            )
            ->update();
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=NOW() ' .
            'WHERE "pKey" IN ( :db_prep_2, :db_prep_3 )',
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
        $query = Factory::query('test');
        $sql = $query->table('table')
            ->where(
                $query->gender->is('F')->or()->status->is('1')
                ->encloseBracket()
                ->openBracket()
                    ->gender->is('M')->or()->status->is('2')
                ->closeBracket()
            )
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
