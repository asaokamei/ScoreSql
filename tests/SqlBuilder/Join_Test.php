<?php
namespace tests\Sql;

use WScore\ScoreSql\Builder\Bind;
use WScore\ScoreSql\Builder\Quote;
use WScore\ScoreSql\Sql\Join;
use WScore\ScoreSql\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Join_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Join
     */
    var $j;

    /**
     * @test
     */
    function left_join_using_key()
    {
        /** @var Join $j */
        $j = Join::left( 'JoinedTable', 'at');
        $j->setQueryTable( 'mt' );
        $j->using( 'myKey' );
        $join = $j->build( new Bind(), new Quote() );
        $this->assertEquals( 'LEFT OUTER JOIN "JoinedTable" "at" USING( "myKey" )', $join );
    }

    /**
     * @test
     */
    function right_join_on_string_criteria()
    {
        /** @var Join $j */
        $j = Join::right( 'JoinedTable', 'at');
        $j->setQueryTable( 'mt' );
        $j->on( 'myKey=youKey AND thisVal=thatVal' );
        $join = $j->build( new Bind(), new Quote() );
        $this->assertEquals( 'RIGHT OUTER JOIN "JoinedTable" "at" ON ( myKey=youKey AND thisVal=thatVal )', $join );
    }

    /**
     * @test
     */
    function right_join_using_and_where_criteria()
    {
        /** @var Join $j */
        $j = Join::left( 'JoinedTable', 'at');
        $j->setQueryTable( 'mt' );
        $j->using('pKey')->on(
            Where::column('myKey')->identical('mt.youKey')->thisVal->identical('mt.thatVal')
        );
        $join = $j->build( new Bind(), new Quote() );
        $this->assertEquals(
            'LEFT OUTER JOIN "JoinedTable" "at" ON ( ' .
            '"at"."pKey"="mt"."pKey" AND ( ' .
            '"at"."myKey" = "mt"."youKey" AND ' .
            '"at"."thisVal" = "mt"."thatVal" ) )', $join );
    }
}