<?php
namespace WScore\ScoreSql\Builder;

use WScore\ScoreSql\Sql\Sql;
use WScore\ScoreSql\Sql\SqlInterface;
use WScore\ScoreSql\Sql\Where;

class GenericUtils
{
    /**
     * @var Bind
     */
    protected $bind = null;

    /**
     * @var Quote
     */
    protected $quote = null;

    /**
     * @var Builder
     */
    protected $builder = null;

    /**
     * @var Sql
     */
    protected $query;

    /**
     * @var string
     */
    protected $quoteChar = '"';

    // +----------------------------------------------------------------------+
    //  construction
    // +----------------------------------------------------------------------+
    /**
     * @param Bind  $bind
     * @param Quote $quote
     * @param Builder $builder
     * @param Sql $query
     */
    public function __construct( $bind, $quote, $builder, $query )
    {
        $this->quote = $quote;
        $this->quote->setQuote( $this->quoteChar );
        $this->bind  = $bind;
        $this->builder = $builder;
        $this->query = $query;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getMagicQuery( $key )
    {
        if( $key == 'getAliasOrTable' ) {
            return $this->query->magicGet('tableAlias') ?: $this->query->magicGet('table');
        }
        return $this->query->magicGet( $key );
    }

    /**
     * @param string|array $name
     * @param string $alias
     * @return string
     */
    protected function quote( $name, $alias=null )
    {
        if( !$this->quote ) return $name;
        if( is_array( $name ) ) return $this->quote->map( $name );
        return $this->quote->quote( $name, $alias );
    }

    /**
     * @param string|array $val
     * @param null|int|string  $col     column name. used to find data type
     * @param null|int         $type    data type
     * @return array|string
     */
    protected function prepare( $val, $col, $type=null )
    {
        return $this->bind->prepare( $val, $col, $type );
    }

    /**
     * @param mixed $string
     * @return string
     */
    protected function evaluate( $string )
    {
        if( $string instanceof \Closure ) {
            return $string();
        } elseif( is_object($string) && $string instanceof SqlInterface ) {
            $string->dbType( $this->builder->getDbType() );
            $builder = new Builder( $this->bind, $this->quote );
            return '( '.$builder->toSql( $string ).' )';
        }
        return null;
    }

    // +----------------------------------------------------------------------+
    //  builders
    // +----------------------------------------------------------------------+
    /**
     * @param string $sqlType
     * @return string
     */
    public function build($sqlType)
    {
        $list = $this->$sqlType;
        return $this->buildByList($list);
    }

    /**
     * @param $list
     * @return string
     */
    protected function buildByList( $list )
    {
        $statement = '';
        foreach ( $list as $item ) {
            $method = 'build' . ucwords( $item );
            if ( $sql = $this->$method() ) {
                $statement .= ' ' . $sql;
            }
        }
        return $statement;
    }

    /**
     * @param array    $list
     * @param \Closure $callable
     * @param string   $sep
     * @return string
     */
    protected function formatList( $list, $callable, $sep=', ' )
    {
        foreach( $list as $col => $val ) {
            $list[$col] = $callable( $col, $val );
        }
        return implode( $sep, $list );
    }

    /**
     * @param string $type
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function formatCriteria( $type )
    {
        if( !$criteria = $this->getMagicQuery($type) ) return '';
        if( !$criteria instanceof Where ) {
            throw new \InvalidArgumentException;
        }
        $criteria->setBuilder( $this->builder );
        $sql = $criteria->build( $this->bind, $this->quote, $this->getMagicQuery('tableAlias'), $this->getMagicQuery('tableParent') );
        return $sql;
    }

    protected function formatInteger($type)
    {
        if( !$integer = $this->getMagicQuery($type) ) return '';
        if( !is_numeric( $integer ) ) return '';
        if( $integer <= 0 ) return '';
        return strtoupper($type) . " " . $integer;
    }

}