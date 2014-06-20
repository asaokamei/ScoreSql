<?php
namespace tests\Sql;

use WScore\SqlBuilder\Builder\Bind;
use WScore\SqlBuilder\Builder\Quote;
use WScore\SqlBuilder\Sql\Where;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Where_Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \WScore\SqlBuilder\Sql\Where
     */
    var $w;

    function setup()
    {
        Bind::reset();
        $this->w = new Where();
    }

    function test0()
    {
        $this->assertEquals( 'WScore\SqlBuilder\Sql\Where', get_class( $this->w ) );
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
            ->ge->ge( 'ge' );
        $where = $this->w->getCriteria();
        $this->assertEquals( [ 'col' => 'eq', 'val' => 'eq', 'rel' => '=',  'op' => 'AND' ], $where[ 0 ] );
        $this->assertEquals( [ 'col' => 'ne', 'val' => 'ne', 'rel' => '!=', 'op' => 'AND' ], $where[ 1 ] );
        $this->assertEquals( [ 'col' => 'lt', 'val' => 'lt', 'rel' => '<',  'op' => 'AND' ], $where[ 2 ] );
        $this->assertEquals( [ 'col' => 'gt', 'val' => 'gt', 'rel' => '>',  'op' => 'AND' ], $where[ 3 ] );
        $this->assertEquals( [ 'col' => 'le', 'val' => 'le', 'rel' => '<=', 'op' => 'AND' ], $where[ 4 ] );
        $this->assertEquals( [ 'col' => 'ge', 'val' => 'ge', 'rel' => '>=', 'op' => 'AND' ], $where[ 5 ] );
    }

    /**
     * @test
     */
    function or_makes_or()
    {
        $sql = Where::column('test')->eq('tested')->or()->more->ne('moreD')->build();
        $this->assertEquals( 'test = tested OR more != moreD', $sql );
    }

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
        $this->assertEquals( 'tested', $bound[':db_prep_1'] );
        $this->assertEquals( 'moreD', $bound[':db_prep_2'] );
        $this->assertEquals( 'good', $bound[':db_prep_3'] );
        $this->assertEquals( 'bad', $bound[':db_prep_4'] );
    }
}
