<?php
namespace tests\Sql;

use WScore\SqlBuilder\Factory;

require_once( dirname( __DIR__ ) . '/autoloader.php' );

class Query_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    function where()
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

}
