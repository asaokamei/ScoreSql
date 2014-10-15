<?php
namespace tests\Sql;

use WScore\ScoreDB\Query;
use WScore\ScoreSql\Builder\Bind;
use WScore\ScoreSql\Builder\Quote;
use WScore\ScoreSql\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Where_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WScore\ScoreSql\Sql\Where
     */
    var $w;

    function setup()
    {
        $this->w = new Where();
    }

    function test0()
    {
        $this->assertEquals( 'WScore\ScoreSql\Sql\Where', get_class( $this->w ) );
    }

    // +----------------------------------------------------------------------+
    //  test various methods
    // +----------------------------------------------------------------------+
    /**
     * @test
     */
    function where_eq()
    {
        $this->w->
            test->eq( 'tested' )->
            more->eq( 'good', 'bad' )->
            some->eq( [ 'more', 'lot' ] );
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '"test" = :db_prep_1 AND "more" IN ( :db_prep_2, :db_prep_3 ) AND "some" IN ( :db_prep_4, :db_prep_5 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 5, count( $bound ) );
        $this->assertEquals( 'tested', $bound[ ':db_prep_1' ] );
        $this->assertEquals( 'good', $bound[ ':db_prep_2' ] );
        $this->assertEquals( 'bad', $bound[ ':db_prep_3' ] );
        $this->assertEquals( 'more', $bound[ ':db_prep_4' ] );
        $this->assertEquals( 'lot', $bound[ ':db_prep_5' ] );
    }
    
    /**
     * @test
     */
    function where_using_call()
    {
        $this->w
            ->eq->eq( 'eq' )
            ->ne->ne( 'ne' )
            ->lt->lt( 'lt' )
            ->gt->gt( 'gt' )
            ->le->le( 'le' )
            ->ge->ge( 'ge' )
            ->notEq->notEq( 'notEq' )
            ->lessThan->lessThan( 'lessThan' )
            ->lessEq->lessEq( 'lessEq' )
            ->greaterEq->greaterEq( 'greaterEq' )
            ->greaterThan->greaterThan( 'greaterThan' )
        ;
        $where = $this->w->getCriteria();
        $this->assertEquals( [ 'col' => 'eq', 'val' => 'eq', 'rel' => '=',  'op' => 'AND' ], $where[ 0 ] );
        $this->assertEquals( [ 'col' => 'ne', 'val' => 'ne', 'rel' => '!=', 'op' => 'AND' ], $where[ 1 ] );
        $this->assertEquals( [ 'col' => 'lt', 'val' => 'lt', 'rel' => '<',  'op' => 'AND' ], $where[ 2 ] );
        $this->assertEquals( [ 'col' => 'gt', 'val' => 'gt', 'rel' => '>',  'op' => 'AND' ], $where[ 3 ] );
        $this->assertEquals( [ 'col' => 'le', 'val' => 'le', 'rel' => '<=', 'op' => 'AND' ], $where[ 4 ] );
        $this->assertEquals( [ 'col' => 'ge', 'val' => 'ge', 'rel' => '>=', 'op' => 'AND' ], $where[ 5 ] );

        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '"eq" = :db_prep_1 AND "ne" != :db_prep_2 AND "lt" < :db_prep_3 AND ' .
            '"gt" > :db_prep_4 AND "le" <= :db_prep_5 AND "ge" >= :db_prep_6 AND ' .
            '"notEq" != :db_prep_7 AND "lessThan" < :db_prep_8 AND "lessEq" <= :db_prep_9 ' . 
            'AND "greaterEq" >= :db_prep_10 AND "greaterThan" > :db_prep_11',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 11, count( $bound ) );
        $this->assertEquals( 'eq', $bound[':db_prep_1'] );
        $this->assertEquals( 'ne', $bound[':db_prep_2'] );
        $this->assertEquals( 'lt', $bound[':db_prep_3'] );
        $this->assertEquals( 'gt', $bound[':db_prep_4'] );
        $this->assertEquals( 'le', $bound[':db_prep_5'] );
        $this->assertEquals( 'ge', $bound[':db_prep_6'] );
        $this->assertEquals( 'notEq', $bound[':db_prep_7'] );
        $this->assertEquals( 'lessThan', $bound[':db_prep_8'] );
        $this->assertEquals( 'lessEq', $bound[':db_prep_9'] );
        $this->assertEquals( 'greaterEq', $bound[':db_prep_10'] );
        $this->assertEquals( 'greaterThan', $bound[':db_prep_11'] );
    }

    /**
     * @test
     */
    function where_in_and_notIn()
    {
        $this->w->test->in( 'tested', 'more' )->more->notIn( 'good', 'bad' );
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '"test" IN ( :db_prep_1, :db_prep_2 ) AND "more" NOT IN ( :db_prep_3, :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'more', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function where_contain_startWith_end_with()
    {
        $this->w->
            test->contain( 'contains' )->
            more->startWith( 'starts' )->
            some->endWith( 'ends' );
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '"test" LIKE :db_prep_1 AND "more" LIKE :db_prep_2 AND "some" LIKE :db_prep_3',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 3, count( $bound ) );
        $this->assertEquals( '%contains%', $bound[':db_prep_1'] );
        $this->assertEquals( 'starts%', $bound[':db_prep_2'] );
        $this->assertEquals( '%ends', $bound[':db_prep_3'] );
    }

    /**
     * @test
     */
    function where_isNull_and_notNull()
    {
        $this->w->test->isNull()->more->notNull();
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '"test" IS NULL AND "more" IS NOT NULL',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 0, count( $bound ) );
    }

    /**
     * @test
     */
    function or_makes_or()
    {
        $sql = Where::column('test')->eq('tested')->or()->more->ne('moreD')->build();
        $this->assertEquals( 'test = tested OR more != moreD', $sql );
    }

    // +----------------------------------------------------------------------+
    //  testing blocks
    // +----------------------------------------------------------------------+
    /**
     * @test
     */
    function and_or_and()
    {
        $this->w
            ->set(
                Where::column( 'test' )->eq( 'tested' )->more->eq( 'moreD' )
            )
            ->or()->set(
                Where::column( 'test' )->eq( 'good' )->more->eq( 'bad' )
            );
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 AND "more" = :db_prep_2 ) OR ( "test" = :db_prep_3 AND "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function and_or_and_using_setting_where()
    {
        $this->w
            ->set(
                Where::column( 'test' )->eq( 'tested' )->more->eq( 'moreD' )
            )
            ->set(
                Where::column( 'test' )->eq( 'good' )->more->eq( 'bad' ), 'or'
            );
        $sql = $this->w->build( $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 AND "more" = :db_prep_2 ) OR ( "test" = :db_prep_3 AND "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function or_and_or()
    {
        $this->w
            ->set(
                Where::column( 'test' )->eq( 'tested' )->or()->more->eq( 'moreD' )
            )
            ->set(
                Where::column( 'test' )->eq( 'good' )->or()->more->eq( 'bad' )
            );
        $sql = $this->w->build(  $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 OR "more" = :db_prep_2 ) AND ( "test" = :db_prep_3 OR "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function block_or_and_or()
    {
        $this->w
            ->openBracket()
                ->test->eq('tested')->or()->more->eq('moreD')
            ->closeBracket()
            ->openBracket()
                ->test->eq('good')->or()->more->eq('bad')
            ->closeBracket();
        $sql = $this->w->build(  $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 OR "more" = :db_prep_2 ) AND ( "test" = :db_prep_3 OR "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function block_and_or_and()
    {
        $this->w
            ->openBracket()
            ->test->eq('tested')->and()->more->eq('moreD')
            ->closeBracket()
            ->orBracket()
            ->test->eq('good')->and()->more->eq('bad')
            ->closeBracket();
        $sql = $this->w->build(  $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 AND "more" = :db_prep_2 ) OR ( "test" = :db_prep_3 AND "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * probably, this is the correct behavior, when forget to 
     * end the block.  
     * 
     * @test
     */
    function block_without_endBlock()
    {
        $w = $this->w
            ->openBracket()
            ->test->eq('tested')->or()->more->eq('moreD')
            ->closeBracket()
            ->openBracket()
            ->test->eq('good')->or()->more->eq('bad');
        $w = $w->getRootParent();
        $sql = $w->build(  $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 OR "more" = :db_prep_2 ) AND "test" = :db_prep_3 OR "more" = :db_prep_4',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function blockSoFar()
    {
        $w = $this->w
            ->test->eq('tested')->more->eq('moreD')
            ->encloseBracket()
            ->orBracket()
            ->test->eq('good')->more->eq('bad')
            ->closeBracket();
        $sql = $w->build(  $bind=new Bind(), new Quote() );
        $this->assertEquals(
            '( "test" = :db_prep_1 AND "more" = :db_prep_2 ) OR ( "test" = :db_prep_3 AND "more" = :db_prep_4 )',
            $sql
        );
        $bound = $bind->getBinding();
        $this->assertEquals( 4, count( $bound ) );
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }

    /**
     * @test
     */
    function where_ends()
    {
        $query = Query::forge()->dbType( 'pgsql' );
        $sql = $query->table( 'table' )->column( 'this', 'that' )
            ->where(
                $query->given('pKey')->eq('1')->or()->pKey->eq('5')
            )
            ->order( 'sort' );
        $this->assertEquals(
            'SELECT "this" AS "that" FROM "table" WHERE "pKey" = :db_prep_1 OR "pKey" = :db_prep_2 ORDER BY "sort" ASC',
            $sql
        );
    }
    // +----------------------------------------------------------------------+
}
