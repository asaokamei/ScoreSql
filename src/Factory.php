<?php
namespace WScore\SqlBuilder;

use WScore\SqlBuilder\Builder\Bind;
use WScore\SqlBuilder\Builder\Builder;
use WScore\SqlBuilder\Builder\Mysql;
use WScore\SqlBuilder\Builder\Pgsql;
use WScore\SqlBuilder\Builder\Quote;

class Factory
{
    /**
     * @param string $dbType
     * @return Query
     */
    public static function query( $dbType=null )
    {
        $builder = static::buildBuilder($dbType);
        return new Query( $builder );
    }

    /**
     * @param Builder $builder
     * @return Query
     */
    public static function buildQuery( $builder=null )
    {
        if( !$builder ) $builder = static::buildBuilder();
        return new Query( $builder );
    }

    /**
     * @param string $dbType
     * @return Builder
     */
    public static function buildBuilder( $dbType=null )
    {
        $dbType = ucwords( $dbType );
        $quote  = static::buildQuote();
        $bind   = static::buildBind();
        if( $dbType == 'Mysql' ) {
            return new Mysql( $quote, $bind );
        }
        if( $dbType == 'Pgsql' ) {
            return new Pgsql( $quote, $bind );
        }
        return new Builder( $quote, $bind );
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