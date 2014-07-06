<?php
namespace WScore\ScoreSql\Builder;

use WScore\ScoreSql\Sql\SqlInterface;
use WScore\ScoreSql\Sql\Where;

class BuildWhere
{
    /**
     * @var Bind
     */
    protected $bind;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Bind    $bind
     * @param Quote   $quote
     * @param Builder $builder
     */
    public function __construct( $bind=null, $quote=null, $builder=null )
    {
        $this->bind = $bind;
        $this->quote = $quote;
        $this->builder = $builder;
    }

    /**
     * @param string $name
     * @param string $alias
     * @return mixed
     */
    public function quote( $name, $alias=null )
    {
        if( !$name ) return $name;
        if( $this->quote ) {
            $name = $this->quote->quote( $name, $alias );
        } elseif( $alias ) {
            $name = $alias . '.' . $name;
        }
        return $name;
    }

    /**
     * @param $value
     * @return array|string
     */
    public function prepare( $value )
    {
        if( $this->bind ) {
            $value = $this->bind->prepare( $value );
        }
        return $value;
    }
    // +----------------------------------------------------------------------+
    /*  build sql statement.

        $this->where = [ [where-info], ...  ]
        where-info contain following columns.
         - op : and or or
         - rel: type of relation. string or callable as raw statement.
         - col: column name.
         - val: value. set false to ignore, callable as raw value.
    */
    // +----------------------------------------------------------------------+
    /**
     * @param Where $criteria
     * @param string $alias
     * @return string
     */
    public function build( $criteria, $alias=null )
    {
        $this->alias = $alias;
        $where = $criteria->getCriteria();
        $sql   = '';
        foreach ( $where as $w ) {
            if ( is_array( $w ) ) {
                $op = isset( $w['op'] ) ? $w['op'] : 'and';
                $sql .= strtoupper($op) . ' '. $this->formWhere( $w );
            } elseif ( is_string( $w ) ) {
                $sql .= 'and ' . $w;
            }
        }
        $sql = trim( $sql );
        $sql = preg_replace( '/^(and|or) /i', '', $sql );
        if( $criteria->getParenthesis() ) {
            $sql = '( ' . $sql . ' )';
        }
        return $sql;
    }

    /**
     * @param array $w
     * @return string
     */
    protected function formWhere( $w )
    {
        $rel = $w[ 'rel' ];
        if ( !$rel ) return '';
        if( $rel instanceof Where ) {
            return $rel->build( $this->bind, $this->quote ) . ' ';
        }
        if( is_callable( $rel ) ) {
            return $rel() . ' ';
        }
        $rel = strtoupper( $rel );

        // making $val based on $rel.
        if ( $rel == 'IN' || $rel == 'NOT IN' ) {
            return $this->buildIn( $w, $rel );
        }
        if ( $rel == 'BETWEEN' ) {
            return $this->buildBetween( $w );
        }

        $col = $w[ 'col' ];
        $val = $w[ 'val' ];
        if ( $rel == 'EQ' ) {

            $val = $this->quote( $val, $this->alias );
            $rel = '=';

        } elseif ( is_callable( $val ) ) {

            $val = $val();

        } elseif ( $val instanceof SqlInterface ) {

            $val = '( ' . $this->builder->toSelect( $val ) . ' )';
            
        } elseif ( $val !== false ) {

            $val = $this->prepare( $val );
        }

        // making $col.
        if( is_string($col) ) {

            $col = $this->quote( $col, $this->alias );

        } elseif( is_callable( $col ) ) {

            $col = $col();
        }
        $where = trim( "{$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

    /**
     * @param $w
     * @return string
     */
    protected function buildBetween( $w )
    {
        $col = $w[ 'col' ];
        $col = $this->quote( $col, $this->alias );
        $val = $w[ 'val' ];
        $val = $this->prepare( $val );
        return "{$col} BETWEEN {$val[0]} AND {$val[1]} ";
    }

    /**
     * @param $w
     * @param $rel
     * @return string
     */
    protected function buildIn( $w, $rel )
    {
        $col = $w[ 'col' ];
        $col = $this->quote( $col, $this->alias );
        $val = $w[ 'val' ];
        $val = $this->prepare( $val );
        $tmp = is_array( $val ) ? implode( ", ", $val ) : $val;
        $val = "( " . $tmp . " )";
        return "{$col} {$rel} {$val} ";
    }
}