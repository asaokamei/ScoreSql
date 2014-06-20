<?php
namespace WScore\SqlBuilder;

/**
 * Class Where
 * @package WScore\DbAccess\Sql
 *
 * @method Where ne( $value )
 * @method Where lt( $value )
 * @method Where le( $value )
 * @method Where gt( $value )
 * @method Where ge( $value )
 * @method Where and()
 * @method Where or()
 */
class Where
{
    /**
     * @var array
     */
    protected $where = array();

    /**
     * @var string
     */
    protected $column;

    protected $andOr = 'AND';

    protected $parenthesis = false;

    protected $methods = [
        'ne'      => '!=',
        'lt'      => '<',
        'gt'      => '>',
        'le'      => '<=',
        'ge'      => '>=',
    ];

    // +----------------------------------------------------------------------+
    //  managing objects.
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct()
    {
    }

    /**
     * @param $method
     * @param $args
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function __call( $method, $args )
    {
        if( $method == 'or' ) {
            $this->andOr = 'OR';
            $this->parenthesis = true;
            return $this;
        }
        if( $method == 'and' ) {
            $this->andOr = 'AND';
            return $this;
        }
        if( isset( $this->methods[$method] ) ) {
            if( in_array( $method, ['isNull', 'notNull'] ) ) {
                return $this->where( $this->column, null, $this->methods[$method] );
            } else {
                return $this->where( $this->column, $args[0], $this->methods[$method] );
            }
        }
        throw new \InvalidArgumentException('no such where relation: '.$method);
    }

    /**
     * @param string $name
     * @return Where
     */
    public static function column( $name )
    {
        /** @var self $where */
        $where = new static;
        $where->col( $name );
        return $where;
    }

    /**
     * @return array
     */
    public function getCriteria() {
        return $this->where;
    }

    /**
     * @param bool $para
     * @return $this
     */
    public function parenthesis( $para=true ) {
        $this->parenthesis = $para;
        return $this;
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
     * @param Bind $bind
     * @param Quote $quote
     * @return string
     */
    public function build( $bind=null, $quote=null )
    {
        $where = $this->where;
        $sql   = '';
        foreach ( $where as $w ) {
            if ( is_array( $w ) ) {
                $op = isset( $w['op'] ) ? $w['op'] : 'and';
                $sql .= strtoupper($op) . ' '. $this->formWhere( $bind, $quote, $w );
            } elseif ( is_string( $w ) ) {
                $sql .= 'and ' . $w;
            }
        }
        $sql = trim( $sql );
        $sql = preg_replace( '/^(and|or) /i', '', $sql );
        if( $this->parenthesis ) {
            $sql = '( ' . $sql . ' )';
        }
        return $sql;
    }

    /**
     * @param Bind $bind
     * @param Quote $quote
     * @param array $w
     * @return string
     */
    protected function formWhere( $bind, $quote, $w )
    {
        $col = $w[ 'col' ];
        $val = $w[ 'val' ];
        $rel = $w[ 'rel' ];
        if ( !$rel ) return '';
        if( $rel instanceof Where ) {
            return $rel->build( $bind, $quote ) . ' ';
        }
        if( is_callable( $rel ) ) {
            return $rel() . ' ';
        }
        $rel = strtoupper( $rel );

        // making $val.
        if ( $rel == 'IN' || $rel == 'NOT IN' ) {

            $val = $bind ? $bind->prepare( $val ) : $val;
            $tmp = is_array( $val ) ? implode( ", ", $val ) : "{$val}";
            $val = "( " . $tmp . " )";

        } elseif ( $rel == 'BETWEEN' ) {

            $val = $bind ? $bind->prepare( $val ) : $val;
            $val = "{$val[0]} AND {$val[1]}";

        } elseif ( is_callable( $val ) ) {

            $val = $val();

        } elseif ( $val !== false ) {

            $val = $bind ? $bind->prepare( $val ) : $val;
        }
        if( is_string($col) ) {
            $col = $quote ? $quote->quote( $col ) : $col;
        } elseif( is_callable( $col ) ) {
            $col = $col();
        }
        $where = trim( "{$col} {$rel} {$val}" ) . ' ';
        return $where;
    }

    // +----------------------------------------------------------------------+
    //  setting columns.
    // +----------------------------------------------------------------------+
    /**
     * set where statement with values properly prepared/quoted.
     *
     * @param string $col
     * @param string $val
     * @param string $rel
     * @param null|string $op
     * @return Where
     */
    public function where( $col, $val, $rel = '=', $op=null )
    {
        if( !$op ) $op = $this->andOr;
        $where          = array( 'col' => $col, 'val' => $val, 'rel' => $rel, 'op' => $op );
        $this->where[ ] = $where;
        return $this;
    }

    /**
     * @param string $name
     * @return Where
     */
    public function __get( $name ) {
        return $this->col( $name );
    }

    /**
     * @param string $col
     * @return Where
     */
    public function col( $col )
    {
        $this->column = $col;
        return $this;
    }

    /**
     * set the where string as is.
     *
     * @param string|Where $where
     * @param null|string  $andOr
     * @return Where
     */
    public function set( $where, $andOr=null )
    {
        if( $where instanceof Where ) {
            if( $where->countCriteria() > 1 ) {
                $where->parenthesis();
            }
            return $this->where( '', false, $where, $andOr );
        }
        return $this->where( '', false, Query::raw($where), $andOr );
    }

    /**
     * @return int
     */
    public function countCriteria()
    {
        return count( $this->where );
    }
    // +----------------------------------------------------------------------+
    //  where clause.
    // +----------------------------------------------------------------------+
    /**
     * @param $val
     * @return Where
     */
    public function eq( $val )
    {
        if ( is_array( $val ) ) {
            return $this->in( $val );
        }
        return $this->where( $this->column, $val, '=' );
    }

    /**
     * @param array $values
     * @return Where
     */
    public function in( $values )
    {
        if( !is_array($values ) ) {
            $values = func_get_args();
        }
        return $this->where( $this->column, $values, 'IN' );
    }

    /**
     * @param $values
     * @return Where
     */
    public function notIn( $values)
    {
        if( !is_array($values ) ) {
            $values = func_get_args();
        }
        return $this->in( $this->column, $values, 'NOT IN' );
    }

    /**
     * @param $val1
     * @param $val2
     * @return Where
     */
    public function between( $val1, $val2 )
    {
        return $this->where( $this->column, [$val1, $val2], "BETWEEN" );
    }

    /**
     * @return Where
     */
    public function isNull()
    {
        return $this->where( $this->column, false, 'IS NULL' );
    }

    /**
     * @return Where
     */
    public function notNull()
    {
        return $this->where( $this->column, false, 'IS NOT NULL' );
    }

    /**
     * @param $val
     * @return Where
     */
    public function like( $val )
    {
        return $this->where( $this->column, $val, 'LIKE' );
    }

    /**
     * @param $val
     * @return Where
     */
    public function contain( $val )
    {
        return $this->like( "%{$val}%" );
    }

    /**
     * @param $val
     * @return Where
     */
    public function startWith( $val )
    {
        return $this->like( "{$val}%" );
    }

    /**
     * @param $val
     * @return Where
     */
    public function endWith( $val )
    {
        return $this->like( "%{$val}" );
    }

}