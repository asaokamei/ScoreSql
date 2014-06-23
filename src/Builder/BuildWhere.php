<?php
namespace WScore\SqlBuilder\Builder;

use WScore\SqlBuilder\Sql\Where;

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

    protected $alias;

    public function __construct( $bind=null, $quote=null )
    {
        $this->bind = $bind;
        $this->quote = $quote;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function quote( $name )
    {
        if( $this->quote ) {
            $name = $this->quote->quote( $name );
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
        $col = $w[ 'col' ];
        $val = $w[ 'val' ];
        $rel = $w[ 'rel' ];
        if ( !$rel ) return '';
        if( $rel instanceof Where ) {
            return $rel->build( $this->bind, $this->quote ) . ' ';
        }
        if( is_callable( $rel ) ) {
            return $rel() . ' ';
        }
        $rel = strtoupper( $rel );

        // making $val.
        if ( $rel == 'IN' || $rel == 'NOT IN' ) {

            $val = $this->prepare( $val );
            $tmp = is_array( $val ) ? implode( ", ", $val ) : "{$val}";
            $val = "( " . $tmp . " )";

        } elseif ( $rel == 'EQ' ) {

            $col = $this->quote( $col );
            $val = $this->quote( $val );
            $rel = '=';

        } elseif ( $rel == 'BETWEEN' ) {

            $val = $this->prepare( $val );
            $val = "{$val[0]} AND {$val[1]}";

        } elseif ( is_callable( $val ) ) {

            $val = $val();

        } elseif ( $val !== false ) {

            $val = $this->prepare( $val );
        }
        if( is_string($col) ) {

            $col = $this->quote( $col );

        } elseif( is_callable( $col ) ) {
            $col = $col();
        }
        $where = trim( "{$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

}