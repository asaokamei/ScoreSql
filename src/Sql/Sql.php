<?php
namespace WScore\SqlBuilder\Sql;

class Sql
{
    /**
     * @var Where
     */
    protected $where;

    /**
     * @var string           name of database table
     */
    public $table;

    /**
     * @var string           name of id (primary key)
     */
    public $keyName;

    /**
     * @var array            join for table
     */
    public $join = [ ];

    /**
     * @var string|array     columns to select in array or string
     */
    public $columns = [ ];

    /**
     * @var array            values for insert/update in array
     */
    public $values = [ ];

    /**
     * @var string[]         such as distinct, for update, etc.
     */
    public $selFlags = [ ];

    /**
     * @var array            order by. [ [ order, dir ], [].. ]
     */
    public $order = [ ];

    /**
     * @var string           group by. [ group, group2, ...]
     */
    public $group = [ ];

    /**
     * @var Where
     */
    public $having;

    /**
     * @var int
     */
    public $limit = null;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var string
     */
    public $returning;

    /**
     * @var string
     */
    public $tableAlias;

    /**
     * @var bool
     */
    public $forUpdate = false;

    // +----------------------------------------------------------------------+
    /**
     * @param $value
     * @return callable
     */
    public static function raw( $value )
    {
        return function () use ( $value ) {
            return $value;
        };
    }

    /**
     * @param Where       $where
     * @param string|null $andOr
     * @return Sql
     */
    public function where( $where, $andOr=null )
    {
        if( !$this->where ) {
            $this->where = $where;
        } else {
            $this->where->set( $where, $andOr );
        }
        return $this;
    }

    /**
     * @param $where
     * @return Sql
     */
    public function whereOr( $where )
    {
        return $this->where( $where, 'or' );
    }

    /**
     * @return Where
     */
    public function getWhere()
    {
        return $this->where;
    }

    // +----------------------------------------------------------------------+
    //  Setting string, array, and data to build SQL statement.
    // +----------------------------------------------------------------------+
    /**
     * @param string $table
     * @param string $alias
     * @return Sql
     */
    public function table( $table, $alias = null )
    {
        $this->table   = $this->table = $table;
        $this->tableAlias = $alias ? : null;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     */
    public function setKeyName( $keyName )
    {
        $this->keyName = $keyName;
    }

    /**
     * @param string $column
     * @param null|string $as
     * @return Sql
     */
    public function column( $column, $as = null )
    {
        if ( $as ) {
            $this->columns[ $as ] = $column;
        } else {
            $this->columns[ ] = $column;
        }
        return $this;
    }

    /**
     * ->columns( [ 'col1', 'col2', ...] )
     * or
     * ->columns( 'col1', 'col2', ... );
     *
     * @param array $column
     * @return $this
     */
    public function columns( $column )
    {
        if( is_array($column ) ) {
            $this->columns += $column;
        } elseif( func_num_args() > 1 ) {
            $column = func_get_args();
            $this->columns += $column;
        }
        return $this;
    }

    /**
     * @param string|array $name
     * @param string|null $value
     * @return $this
     */
    public function value( $name, $value = null )
    {
        if ( is_array( $name ) ) {
            $this->values = $name;
        } elseif ( func_num_args() > 1 ) {
            $this->values[ $name ] = $value;
        }
        return $this;
    }

    /**
     * @param string $order
     * @param string $sort
     * @return $this
     */
    public function order( $order, $sort = 'ASC' )
    {
        $this->order[ ] = [ $order, $sort ];
        return $this;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function group( $group )
    {
        $this->group[ ] = $group;
        return $this;
    }

    /**
     * @param Where $having
     * @return $this
     */
    public function having( $having )
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit( $limit )
    {
        $this->limit = ( is_numeric( $limit ) ) ? $limit : null;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset( $offset )
    {
        $this->offset = ( is_numeric( $offset ) ) ? $offset : 0;
        return $this;
    }

    /**
     * creates SELECT DISTINCT statement.
     * @return Sql
     */
    public function distinct()
    {
        return $this->flag( 'DISTINCT' );
    }

    /**
     * @param bool $for
     * @return Sql
     */
    public function forUpdate( $for = true )
    {
        $this->forUpdate = $for;
        return $this;
    }

    /**
     * @param $flag
     * @return $this
     */
    public function flag( $flag )
    {
        $this->selFlags[ ] = $flag;
        return $this;
    }

    /**
     * @param string $return
     * @return $this
     */
    public function returning( $return )
    {
        $this->returning = $return;
        return $this;
    }
    // +----------------------------------------------------------------------+
}