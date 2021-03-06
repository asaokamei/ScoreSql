<?php

namespace tests\Sql;

use PHPUnit\Framework\TestCase;
use WScore\ScoreSql\DB;
use WScore\ScoreSql\Query;
use WScore\ScoreSql\Sql\Where;

require_once(dirname(__DIR__) . '/autoloader.php');

class Query_Test extends TestCase
{
    /**
     * @test
     */
    function simple_example_of_select()
    {
        $sql = DB::from('myTable')
            ->column('col1', 'aliased1')
            ->columns('col2', 'col3')
            ->where(
                DB::given('status')->is('4')
            );
        $this->assertEquals(
            'SELECT "col1" AS "aliased1", "col2", "col3" FROM "myTable" WHERE "status" = :db_prep_1',
            (string)$sql);
        $this->assertEquals([':db_prep_1' => 4], $sql->getBind());
    }

    /**
     * @test
     */
    function simple_example_of_insert()
    {
        $sql = DB::from('myTable')
            ->value(['col1' => 'val1', 'col2' => 'val2'])
            ->toInsert();
        $this->assertEquals(
            'INSERT INTO "myTable" ( "col1", "col2" ) VALUES ( :db_prep_1, :db_prep_2 )',
            (string)$sql);
        $this->assertEquals([':db_prep_1' => 'val1', ':db_prep_2' => 'val2'], $sql->getBind());
    }

    /**
     * @test
     */
    function simple_example_of_update()
    {
        $sql = DB::from('myTable');
        $sql->where(
            DB::given('name')->like('bob')->or()->status->eq('1')
        )
            ->value([
                'date' => $sql->raw('NOW()'),
                'col2' => 'val2'
            ])
            ->toUpdate();

        $this->assertEquals(
            'UPDATE "myTable" SET "date"=NOW(), "col2"=:db_prep_1 WHERE "name" LIKE :db_prep_2 OR "status" = :db_prep_3',
            $sql);
        $this->assertEquals([':db_prep_1' => 'val2', ':db_prep_2' => 'bob', ':db_prep_3' => '1'], $sql->getBind());
    }

    /**
     * @test
     */
    function select_builds_select_statement()
    {
        $sql = Query::forge()->dbType('pgsql')->table('myTable')
            ->where(
                DB::given('pKey')->eq('1')
                    ->orBracket()
                    ->name->startWith('AB')->gender->eq('F')
                    ->closeBracket()
            );
        $this->assertEquals(
            'SELECT * FROM "myTable" ' .
            'WHERE "pKey" = :db_prep_1 OR ( "name" LIKE :db_prep_2 AND "gender" = :db_prep_3 )',
            $sql);
        $this->assertEquals([':db_prep_1' => '1', ':db_prep_2' => 'AB%', ':db_prep_3' => 'F'], $sql->getBind());
    }

    /**
     * @test
     */
    function insert_builds_insert_statement()
    {
        $sql = Query::forge()->dbType('mysql')->table('myTable')
            ->value(['test' => 'tested', 'more' => 'done'])->toInsert();
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql);
        $this->assertEquals([':db_prep_1' => 'tested', ':db_prep_2' => 'done'], $sql->getBind());

        $query = Query::forge()->dbType('mysql')->table('myTable');
        $query->test = 'tested2';
        $query->more = 'done2';
        $sql = $query->toInsert();
        $this->assertEquals(
            'INSERT INTO `myTable` ( `test`, `more` ) VALUES ( :db_prep_1, :db_prep_2 )',
            $sql);
        $this->assertEquals([':db_prep_1' => 'tested2', ':db_prep_2' => 'done2'], $sql->getBind());
    }

    /**
     * @test
     */
    function update_builds_update_statement()
    {
        $sql = DB::from('myTable')
            ->where(
                Where::column('pKey')->in('1', '2')
            )
            ->value(['test' => 'tested', 'more' => 'done'])
            ->toUpdate();
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=:db_prep_2 ' .
            'WHERE "pKey" IN ( :db_prep_3, :db_prep_4 )',
            $sql);
        $this->assertEquals(
            [':db_prep_1' => 'tested', ':db_prep_2' => 'done', ':db_prep_3' => '1', ':db_prep_4' => '2'],
            $sql->getBind());

        $query = DB::from('myTable');
        $query->test = 'tested';
        $query->more = $query->raw('NOW()');
        $sql = $query
            ->where(
                Where::column('pKey')->in('1', '2')
            )
            ->toUpdate();
        $this->assertEquals(
            'UPDATE "myTable" SET "test"=:db_prep_1, "more"=NOW() ' .
            'WHERE "pKey" IN ( :db_prep_2, :db_prep_3 )',
            $sql);
        $this->assertEquals(
            [':db_prep_1' => 'tested', ':db_prep_2' => '1', ':db_prep_3' => '2'],
            $sql->getBind());
    }

    /**
     * @test
     */
    function delete_builds_delete_statement()
    {
        $sql = Query::forge();
        $sql->dbType('mysql')->table('myTable', 'mt')->keyName('myKey')
            ->where($sql->given('myKey')->eq('3'))->toDelete();
        $this->assertEquals(
            'DELETE FROM `myTable` WHERE `mt`.`myKey` = :db_prep_1',
            (string)$sql);
    }

    /**
     * in README.md
     *
     * @test
     */
    function cool()
    {
        $sql = DB::from('table')
            ->where(
                Where::bracket()
                    ->gender->is('F')->or()->status->is('1')
                    ->close()
                    ->open()
                    ->gender->is('M')->or()->status->is('2')
                    ->close()
            )
            ->order('id')
            ->limit(5);
        $this->assertEquals(
            'SELECT * FROM "table" WHERE ' .
            '( "gender" = :db_prep_1 OR "status" = :db_prep_2 ) AND ' .
            '( "gender" = :db_prep_3 OR "status" = :db_prep_4 ) ' .
            'ORDER BY "id" ASC LIMIT 5',
            $sql);
    }

    /**
     * @test
     */
    function query_with_join_using()
    {
        $sql = DB::from('table1')
            ->join(DB::join('another')->using('key'))
            ->where(DB::given('key')->is(1));
        $this->assertEquals(
            'SELECT * FROM "table1" JOIN "another" USING( "key" ) WHERE "key" = :db_prep_1',
            (string)$sql
        );
    }

    /**
     * @test
     */
    function query_with_leftJoin_on()
    {
        $sql = DB::from('table1')
            ->join(DB::join('another', 'an')->left()->on(DB::given('thisKey')->identical('$.thatKey')))
            ->where(DB::given('key')->is(1));
        $this->assertEquals(
            'SELECT * FROM "table1" ' .
            'LEFT OUTER JOIN "another" "an" ON ( "an"."thisKey" = "table1"."thatKey" ) ' .
            'WHERE "key" = :db_prep_1',
            (string)$sql
        );
    }

    /**
     * @test
     */
    function query_with_rightJoin_on_using()
    {
        $sql = DB::from('table1')
            ->join(DB::join('another', 'an')->right()->using('key')->on(DB::given('$.thisKey')->identical('thatKey')))
            ->where(DB::given('key')->is(1));
        $this->assertEquals(
            'SELECT * FROM "table1" ' .
            'RIGHT OUTER JOIN "another" "an" ON ( "an"."key"="table1"."key" AND ( "table1"."thisKey" = "an"."thatKey" ) ) ' .
            'WHERE "key" = :db_prep_1',
            (string)$sql
        );
    }

    /**
     * @test
     */
    function sub_query_in_column()
    {
        $query = DB::from('main');
        $query->column(
            $query->subQuery('sub')
                ->column($query->raw('COUNT(*)'), 'count')
                ->where($query->given('status')->identical('$.status')),
            'count_sub'
        );
        $sql = $query->toSelect();
        $this->assertEquals(
            'SELECT ( ' .
            'SELECT COUNT(*) AS "count" FROM "sub" AS "sub_1" WHERE "sub_1"."status" = "main"."status"' .
            ' ) AS "count_sub" FROM "main"', (string)$sql);
    }

    /**
     * @test
     */
    function sub_query_as_table()
    {
        $query = new Query;
        $query->table($query->subQuery('sub')->where(DB::given('status')->is(1)))
            ->where(
                DB::given('name')->is('bob')
            );
        $sql = $query->toSelect();
        $this->assertEquals(
            'SELECT * FROM ( ' .
            'SELECT * FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1' .
            ' ) WHERE "name" = :db_prep_2', (string)$sql);
    }

    /**
     * @test
     */
    function sub_query_in_where_is()
    {
        $query = DB::from('main');
        $query->where(
            $query->given('status')->is(
                $query->subQuery('sub')->column('status')->where($query->given('name')->is('bob'))
            )
        );
        $sql = $query->toSelect();
        $this->assertEquals(
            'SELECT * FROM "main" WHERE "status" = ( ' .
            'SELECT "status" FROM "sub" AS "sub_1" WHERE "sub_1"."name" = :db_prep_1 )', (string)$sql);
    }

    /**
     * @test
     */
    function sub_query_in_update_set_and_insert()
    {
        $sql = DB::from('main');
        $sql->value('count',
            $sql->subQuery('sub')->column($sql->raw('COUNT(*)'))->where($sql->given('status')->is(1)))->toUpdate();
        $this->assertEquals(
            'UPDATE "main" SET "count"=( SELECT COUNT(*) FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1 )',
            (string)$sql);

        $sql = DB::from('main');
        $sql->value('count',
            $sql->subQuery('sub')->column($sql->raw('COUNT(*)'))->where($sql->given('status')->is(1)))->toInsert();
        $this->assertEquals(
            'INSERT INTO "main" ( "count" ) VALUES ( ( SELECT COUNT(*) FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1 ) )',
            (string)$sql);
    }
}
