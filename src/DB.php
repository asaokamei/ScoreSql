<?php
namespace WScore\ScoreSql;

use WScore\ScoreSql\Builder\Builder;
use WScore\ScoreSql\Sql\Sql;
use WScore\ScoreSql\Sql\Where;

/**
 * Class Query
 *
 * @package WScore\ScoreSql
 *          
 */
class DB
{
    /**
     * @var Query
     */
    static $query;
    
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $dbType;

    /**
     * 
     */
    public static function refresh()
    {
        static::$query = null;
    }

    /**
     * @param bool $new
     * @return Query
     */
    protected static function getQuery( $new=false )
    {
        if( !static::$query || $new ) {
            static::$query = Factory::buildQuery();
        }
        return static::$query;
    }
    
    // +----------------------------------------------------------------------+
    //  manage objects, aka Facade.
    // +----------------------------------------------------------------------+
    /**
     * @param string $dbType
     * @return Query
     */
    public static function db( $dbType=null )
    {
        $self = static::getQuery(true);
        $self->dbType( $dbType );
        static::$query = $self;
        return $self;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return Query
     */
    public static function from( $table, $alias = null )
    {
        $self = static::getQuery(true);
        $self->table( $table, $alias );
        static::$query = $self;
        return $self;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return Sql
     */
    public static function subQuery( $table, $alias = null )
    {
        return static::getQuery()->sub( $table, $alias );
    }

    /**
     * @param $value
     * @return callable
     */
    public static function raw( $value )
    {
        return Sql::raw( $value );
    }
    
    /**
     * @return array
     */
    public static function bind()
    {
        return static::$query->getBind();
    }

    /**
     * @param string $column
     * @return Where
     */
    public static function given( $column=null )
    {
        return Where::column($column);
    }
}
