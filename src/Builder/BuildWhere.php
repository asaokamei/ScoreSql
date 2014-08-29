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
    protected $aliasedTableName;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $parentTableName;

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
     * @return string
     */
    public function quote( $name )
    {
        if( !$name ) return $name;
        if( $this->quote ) {
            $name = $this->quote->quote( $name, $this->aliasedTableName, $this->parentTableName );
        } elseif( $this->aliasedTableName ) {
            $name = $this->aliasedTableName . '.' . $name;
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
     * @param string $parent
     * @throws \InvalidArgumentException
     * @return string
     */
    public function build( $criteria, $alias=null, $parent=null )
    {
        $this->aliasedTableName = $alias;
        $this->parentTableName  = $parent;
        $where_list = $criteria->getCriteria();
        $sql   = '';

        foreach ( $where_list as $where ) {

            if ( is_string( $where ) ) {
                $sql .= 'and ' . $where;
                continue;
            }
            if ( !is_array( $where ) ) {
                throw new \InvalidArgumentException;
            }
            $op = isset( $where['op'] ) ? $where['op'] : 'and';
            $sql .= strtoupper($op) . ' '. $this->formWhere( $where );
        }
        $sql = trim( $sql );
        $sql = preg_replace( '/^(and|or) /i', '', $sql );
        if( $criteria->getParenthesis() ) {
            $sql = '( ' . $sql . ' )';
        }
        return $sql;
    }

    /**
     * @param array $where
     * @return string
     */
    protected function formWhere( $where )
    {
        $rel = $where[ 'rel' ];
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
            return $this->buildIn( $where, $rel );
        }
        if ( $rel == 'BETWEEN' ) {
            return $this->buildBetween( $where );
        }
        $where[ 'rel' ] = $rel;
        return $this->buildColRelVal( $where );
    }

    /**
     * for normal case where condition, like col = val
     *
     * @param array $where
     * @return string
     */
    protected function buildColRelVal( $where )
    {
        $rel = $where[ 'rel' ];
        $col = $where[ 'col' ];
        $val = $where[ 'val' ];
        if ( $rel == 'EQ' ) {
            // EQ: equal to another column (i.e. val is an identifier.)
            $val = $this->quote( $val );
            $rel = '=';
        } else {
            $val = $this->formWhereVal( $val );
        }
        // normal case. compose where like col = val
        $col = $this->formWhereCol( $col );

        $where = trim( "{$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

    /**
     * @param $where
     * @return string
     */
    protected function buildBetween( $where )
    {
        $col = $where[ 'col' ];
        $col = $this->quote( $col );
        $val = $where[ 'val' ];
        $val = $this->prepare( $val );
        return "{$col} BETWEEN {$val[0]} AND {$val[1]} ";
    }

    /**
     * @param $where
     * @param string $rel
     * @return string
     */
    protected function buildIn( $where, $rel )
    {
        $col = $where[ 'col' ];
        $col = $this->quote( $col );
        $val = $where[ 'val' ];
        $val = $this->prepare( $val );
        $tmp = is_array( $val ) ? implode( ", ", $val ) : $val;
        $val = "( " . $tmp . " )";
        return "{$col} {$rel} {$val} ";
    }

    /**
     * @param mixed $val
     * @return string
     */
    protected function formWhereVal( $val )
    {
        if ( is_callable( $val ) ) {
            return $val();
        }
        if ( $val instanceof SqlInterface ) {
            return '( ' . $this->builder->toSql( $val ) . ' )';
        }
        if ( $val !== false ) {
            return $this->prepare( $val );
        }
        return '';
    }

    /**
     * @param mixed $col
     * @return string
     */
    protected function formWhereCol( $col )
    {
        // making $col.
        if ( is_string( $col ) ) {

            $col = $this->quote( $col );
            return $col;

        } elseif ( is_callable( $col ) ) {

            return $col();
        }
        return $col;
    }
}