<?php
namespace WScore\ScoreSql;

use WScore\ScoreSql\Builder\Bind;
use WScore\ScoreSql\Builder\Builder;
use WScore\ScoreSql\Builder\Mysql;
use WScore\ScoreSql\Builder\Pgsql;
use WScore\ScoreSql\Builder\Quote;

class Factory
{
    /**
     * @param string $dbType
     * @return Query
     */
    public static function query( $dbType=null )
    {
        $builder = static::buildBuilder($dbType);
        return static::buildQuery( $builder );
    }

    /**
     * @param Builder $builder
     * @return Query
     */
    public static function buildQuery( $builder=null )
    {
        if( !$builder ) $builder = static::buildBuilder();
        $query = new Query( $builder );
        $query->setBuilder( $builder );
        return $query;
    }

    /**
     * @param string $dbType
     * @return Builder
     */
    public static function buildBuilder( $dbType=null )
    {
        $dbType = ucwords( $dbType );
        $bind   = static::buildBind();
        $quote  = static::buildQuote();
        if( $dbType == 'Mysql' ) {
            return new Mysql( $bind, $quote );
        }
        if( $dbType == 'Pgsql' ) {
            return new Pgsql( $bind, $quote );
        }
        return new Builder( $bind, $quote );
    }

    /**
     * @return Quote
     */
    protected static function buildQuote()
    {
        return new Quote();
    }

    /**
     * @return Bind
     */
    protected static function buildBind()
    {
        return new Bind();
    }

}