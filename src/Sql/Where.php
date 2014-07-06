<?php
namespace WScore\ScoreSql\Sql;

use WScore\ScoreSql\Builder\Bind;
use WScore\ScoreSql\Builder\Builder;
use WScore\ScoreSql\Builder\BuildWhere;
use WScore\ScoreSql\Builder\Quote;

/**
 * Class Where
 * @package WScore\DbAccess\Sql
 *
 * @method Where is( $value )
 * @method Where eq( $value )
 * @method Where ne( $value )
 * @method Where lt( $value )
 * @method Where le( $value )
 * @method Where gt( $value )
 * @method Where ge( $value )
 * @method Where notEq( $value )
 * @method Where lessThan( $value )
 * @method Where lessEq( $value )
 * @method Where greaterThan( $value )
 * @method Where greaterEq( $value )
 * @method Where and()
 * @method Where or()
 * @method Where open()
 * @method Where close()
 * @method Where openOr()
 * @method Where enclose()
 */
class Where
{
    /**
     * @var array
     */
    protected $where = array();

    /**
     * @var Where
     */
    protected $parent = null;

    /**
     * @var string
     */
    protected $column;

    protected $andOr = 'AND';

    protected $parenthesis = false;

    protected $method2rel = [
        'ne'      => '!=',
        'lt'      => '<',
        'gt'      => '>',
        'le'      => '<=',
        'ge'      => '>=',
        'notEq'      => '!=',
        'lessThan'      => '<',
        'greaterThan'      => '>',
        'lessEq'      => '<=',
        'greaterEq'      => '>=',
    ];

    protected $method2me = [
        'eq'   => 'equal',
        'is'   => 'equal',
        'open'    => 'openBracket',
        'close'   => 'closeBracket',
        'openOr'  => 'orBracket',
        'enclose' => 'encloseBracket',
    ];

    /**
     * @var Builder
     */
    protected $builder;

    // +----------------------------------------------------------------------+
    //  managing objects.
    // +----------------------------------------------------------------------+
    /**
     */
    public function __construct()
    {
    }

    /**
     * @param Builder $query
     */
    public function setBuilder( $query )
    {
        $this->builder = $query;
    }

    /**
     * @param Where $parent
     */
    public function setParent( $parent )
    {
        $this->parent = $parent;
    }

    /**
     * @return Where
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Where
     */
    public function getRootParent()
    {
        $top = $this;
        while( $parent = $top->getParent() ) {
            $top = $parent;
        }
        return $top;
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
            return $this;
        }
        if( $method == 'and' ) {
            $this->andOr = 'AND';
            return $this;
        }
        if( isset( $this->method2me[$method] ) ) {
            return call_user_func_array( [$this,$this->method2me[$method]], $args );
        }
        if( isset( $this->method2rel[$method] ) ) {
            return $this->where( $this->column, $args[0], $this->method2rel[$method] );
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
     * @return Where
     */
    public static function bracket()
    {
        /** @var self $where */
        $where = new static;
        return $where->openBracket();
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

    /**
     * @return bool
     */
    public function getParenthesis()
    {
        return $this->parenthesis;
    }

    /**
     * @return Where
     */
    public function encloseBracket()
    {
        $where = new self;
        $where->set( $this );
        return $where;
    }

    /**
     * @return Where
     */
    public function orBracket()
    {
        return $this->openBracket('or');
    }

    /**
     * @param string $andOr
     * @return Where
     */
    public function openBracket($andOr='and')
    {
        $block = new self;
        $this->set( $block, $andOr );
        return $block;
    }

    /**
     * @return $this|Where
     */
    public function closeBracket()
    {
        if( !$parent = $this->getParent() ) {
            return $this;
        }
        if( $this->countCriteria() > 1 ) {
            $this->parenthesis();
        }
        return $parent;
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
     * @param Bind   $bind
     * @param Quote  $quote
     * @param string $alias
     * @param string $parent
     * @return string
     */
    public function build( $bind=null, $quote=null, $alias=null, $parent=null )
    {
        $builder = new BuildWhere( $bind, $quote, $this->builder );
        return $builder->build( $this, $alias, $parent );
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
            $where->setParent( $this );
            return $this->where( '', false, $where, $andOr );
        }
        return $this->where( '', false, Sql::raw($where), $andOr );
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
     * for equal columns, i.e. myColumn=youColumn.
     *
     * @param string $column
     * @return Where
     */
    public function identical( $column ) {
        return $this->where( $this->column, $column, 'eq' );
    }

    /**
     * @param $val
     * @return Where
     */
    public function equal( $val )
    {
        if( func_num_args() > 1 ) {
            return $this->where( $this->column, func_get_args(), 'IN' );
        }
        if ( is_array( $val ) ) {
            return $this->where( $this->column, $val, 'IN' );
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
        return $this->where( $this->column, $values, 'NOT IN' );
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