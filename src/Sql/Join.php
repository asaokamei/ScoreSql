<?php
namespace WScore\ScoreSql\Sql;

use WScore\ScoreSql\Builder\Bind;
use WScore\ScoreSql\Builder\Quote;

class Join implements JoinInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $type = 'JOIN';

    /**
     * @var string
     */
    protected $usingKey;

    /**
     * @var string|Where
     */
    protected $criteria;

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
     * @param string $table
     * @param string $alias
     */
    public function __construct( $table, $alias=null )
    {
        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return JoinInterface
     */
    public static function table( $table, $alias=null )
    {
        $join = new self( $table, $alias );
        return $join;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return JoinInterface
     */
    public static function left( $table, $alias=null )
    {
        $join = new self( $table, $alias );
        $join->by( 'LEFT OUTER JOIN' );
        return $join;
    }

    /**
     * @param string $table
     * @param string $alias
     * @return JoinInterface
     */
    public static function right( $table, $alias )
    {
        $join = new self( $table, $alias );
        $join->by( 'RIGHT OUTER JOIN' );
        return $join;
    }

    /**
     * @param string $type
     * @return JoinInterface
     */
    public function by( $type )
    {
        $this->type = $type;
        return $this;
    }

    /**
     * for setting parent query's table or alias name.
     * will be used in Sql::join method.
     *
     * @param string $queryTable
     * @return $this
     */
    public function setQueryTable( $queryTable )
    {
        $this->queryTable = $queryTable;
        return $this;
    }

    /**
     * @param string $key
     * @return JoinInterface
     */
    public function using( $key )
    {
        $this->usingKey = $key;
        return $this;
    }

    /**
     * @param Where|string $criteria
     * @return JoinInterface
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
            $sql .= $this->criteria->build( $this->bind, $this->quote, $this->alias, $this->queryTable );
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