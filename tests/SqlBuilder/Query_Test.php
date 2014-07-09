<?php
namespace tests\Sql;

use WScore\ScoreSql\Query;
use WScore\ScoreSql\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function simple_example_of_select()
    {
        $sql = Query::from('myTable')
            ->column('col1', 'aliased1')
            ->columns( 'col2', 'col3' )
            ->where(
                Query::given('status')->is('4')
            );
        ;
        $this->assertEquals(
            'SELECT "col1" AS "aliased1", "col2", "col3" FROM "myTable" WHERE "status" = :db_prep_1',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => 4 ], Query::bind() );
    }

    /**
     * @test
     */
    function simple_example_of_insert()
    {
        $sql = Query::from('myTable')
            ->value( [ 'col1' => 'val1', 'col2'=>'val2' ] )
            ->toInsert();
        ;
        $this->assertEquals(
            'INSERT INTO "myTable" ( "col1", "col2" ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => 'val1', ':db_prep_2' => 'val2' ], Query::bind() );
    }

    /**
     * @test
     */
    function simple_example_of_update()
    {
        $sql = Query::from('myTable')
            ->where(
                Query::given('name')->like('bob')->or()->status->eq('1')
            )
            ->value( [
                'date' => Query::raw('NOW()'),
                'col2'=>'val2'
            ] )
            ->toUpdate();

        $this->assertEquals(
            'UPDATE "myTable" SET "date"=NOW(), "col2"=:db_prep_1 WHERE "name" LIKE :db_prep_2 OR "status" = :db_prep_3',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => 'val2', ':db_prep_2' => 'bob', ':db_prep_3' => '1' ], Query::bind() );
    }

    /**
     * @test
     */
    function select_builds_select_statement()
    {
        $sql = Query::db( 'pgsql' )->table( 'myTable' )
            ->where(
                Query::given('pKey')->eq('1')
                    ->orBracket()
                    ->name->startWith('AB')->gender->eq('F')
                    ->closeBracket()
            );
        ;
        $this->assertEquals(
            'SELECT * FROM "myTable" ' .
            'WHERE "pKey" = :db_prep_1 OR ( "name" LIKE :db_prep_2 AND "gender" = :db_prep_3 )',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => '1', ':db_prep_2' => 'AB%', ':db_prep_3' => 'F' ], Query::bind() );
    }

    /**
     * @test
     */
    function insert_builds_insert_statement()
    {
        $sql = Query::db( 'mysql' )->table( 'myTable' )
            ->value(['test'=>'tested', 'more'=>'done'])->toInsert();
        ;
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => 'tested', ':db_prep_2' => 'done' ], Query::bind() );

        $query = Query::db('mysql')->table('myTable');
        $query->test = 'tested2';
        $query->more = 'done2';
        $sql = $query->toInsert();
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql );
        $this->assertEquals( [ ':db_prep_1' => 'tested2', ':db_prep_2' => 'done2' ], Query::bind() );
    }

    /**
     * @test
     */
    function update_builds_update_statement()
    {
        $sql = Query::from( 'myTable' )
            ->where(
                Where::column('pKey')->in( '1', '2' )
            )
            ->value(['test'=>'tested', 'more'=>'done'])
            ->toUpdate();
        ;
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=:db_prep_2 ' .
            'WHERE "pKey" IN ( :db_prep_3, :db_prep_4 )',
            $sql );
        $this->assertEquals( 
            [ ':db_prep_1' => 'tested', ':db_prep_2' => 'done', ':db_prep_3' => '1', ':db_prep_4' => '2' ], 
            Query::bind() );

        $query = Query::from('myTable');
        $query->test = 'tested';
        $query->more = $query->raw('NOW()');
        $sql = $query
            ->where(
                Where::column('pKey')->in( '1', '2' )
            )
            ->toUpdate();
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=NOW() ' .
            'WHERE "pKey" IN ( :db_prep_2, :db_prep_3 )',
            $sql );
        $this->assertEquals(
            [ ':db_prep_1' => 'tested', ':db_prep_2' => '1', ':db_prep_3' => '2' ],
            Query::bind() );
    }

    /**
     * @test
     */
    function delete_builds_delete_statement()
    {
        $sql = Query::db( 'mysql' )->table( 'myTable', 'mt' )->keyName('myKey')
            ->where( Query::given('myKey')->eq('3') )->toDelete();
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
        $sql = Query::from('table')
            ->where(
                Where::bracket()
                    ->gender->is('F')->or()->status->is('1')
                ->close()
                ->open()
                    ->gender->is('M')->or()->status->is('2')
                ->close()
            )
            ->order( 'id' )
            ->limit(5);
        $this->assertEquals(
            'SELECT * FROM "table" WHERE ' .
            '( "gender" = :db_prep_1 OR "status" = :db_prep_2 ) AND ' .
            '( "gender" = :db_prep_3 OR "status" = :db_prep_4 ) ' .
            'ORDER BY "id" ASC LIMIT :db_prep_5',
            $sql );
    }
}
