<?php
namespace WScore\SqlBuilder\Sql;

use WScore\SqlBuilder\Builder\Bind;
use WScore\SqlBuilder\Builder\Quote;

class Join
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var string
     */
    public $type = 'JOIN';

    /**
     * @var string
     */
    public $usingKey;

    /**
     * @var string|Where
     */
    public $criteria;

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
    protected $queryTable;

    // +----------------------------------------------------------------------+
    //  managing objects.
    // +----------------------------------------------------------------------+
    /**
     * @param string $queryTable
     * @param string $table
     * @param string $alias
     */
    public function __construct( $queryTable, $table, $alias=null )
    {
        $this->queryTable = $queryTable;
        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @return $this
     */
    public function left()
    {
        $this->type = 'LEFT OUTER JOIN';
        return $this;
    }

    /**
     * @return $this
     */
    public function right()
    {
        $this->type = 'RIGHT OUTER JOIN';
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function using( $key )
    {
        $this->usingKey = $key;
        return $this;
    }

    /**
     * @param Where|string $criteria
     * @return $this
     */
    public function on( $criteria )
    {
        $this->criteria = $criteria;
        return $this;
    }

    // +----------------------------------------------------------------------+
    //  build sql statement.
    // +----------------------------------------------------------------------+
    /**
     * @param Bind $bind
     * @param Quote $quote
     * @return string
     */
    public function build( $bind=null, $quote=null )
    {
        $this->bind  = $bind;
        $this->quote = $quote;
        $join = [
            $this->buildJoinType(),
            $this->buildTable(),
            $this->buildUsingOrOn(),
        ];
        return implode( ' ', $join );
    }

    /**
     * @param string|array $name
     * @return string
     */
    protected function quote( $name )
    {
        if( !$this->quote ) return $name;
        if( is_array( $name ) ) return $this->quote->map( $name );
        return $this->quote->quote( $name );
    }

    /**
     * @return string
     */
    protected function alias()
    {
        return $this->alias ?: $this->table;
    }

    /**
     * @return string
     */
    protected function buildJoinType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    protected function buildTable()
    {
        $table = $this->quote( $this->table );
        if( $this->alias ) {
            $table .= ' ' . $this->quote( $this->alias );
        }
        return $table;
    }

    /**
     * @return string
     */
    protected function buildUsingOrOn()
    {
        if( $this->criteria ) {
            return $this->buildOn();
        }
        if( $this->usingKey ) {
            return $this->buildUsing();
        }
        return '';
    }

    /**
     * @return string
     */
    protected function buildUsing()
    {
        return 'USING( ' . $this->quote( $this->usingKey ) . ' )';
    }

    /**
     * @return string
     */
    protected function buildOn()
    {
        $sql = '';
        if( is_object( $this->criteria ) && $this->criteria instanceof Where ) {
            $sql .= $this->criteria->build( $this->bind, $this->quote, $this->alias );
        }
        elseif( is_string( $this->criteria ) ) {
            $sql .= (string) $this->criteria;
        }
        if( $this->usingKey ) {
            $sql = $this->quote( $this->alias() . '.' . $this->usingKey ) .
                '=' .
                $this->quote( $this->queryTable . '.' . $this->usingKey ) .
                ' AND ( ' . $sql . ' )';
        }
        return 'ON ( ' . $sql . ' )';
    }
}