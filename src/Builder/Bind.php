<?php
namespace WScore\ScoreSql\Builder;

class Bind
{
    /**
     * @var bool
     */
    public static $useColumnInBindValues = false;
    
    /**
     * @var int
     */
    protected $prepared_counter = 1;

    /**
     * @var array    stores prepared values and holder name
     */
    protected $prepared_values = array();

    /**
     * @var array    stores data types of place holders
     */
    protected $prepared_types = array();

    /**
     * @var array    stores data types of columns
     */
    protected $col_data_types = array();

    /**
     * @param $column
     * @param $type
     */
    public function setColumnType( $column, $type )
    {
        $this->col_data_types[$column] = $type;
    }

    // +----------------------------------------------------------------------+
    //  preparing for Insert and Update statement.
    // +----------------------------------------------------------------------+
    /**
     * replaces value with place holder for prepared statement.
     * the value is kept in prepared_value array.
     *
     * if $type is specified, or column data type is set in col_data_types,
     * types for the place holder is kept in prepared_types array.
     *
     * @param string|array $val
     * @param null|int|string  $col     column name. used to find data type
     * @param null|int         $type    data type
     * @return string|array
     */
    public function prepare( $val, $col=null, $type=null )
    {
        if( is_array( $val ) ) {
            $holder = [];
            foreach( $val as $key => $v ) {
                $holder[$key] = $this->prepare( $v, $col, $type );
            }
            return $holder;
        }
        if( is_callable( $val ) ) return $val;

        $holder  = ( static::$useColumnInBindValues ) ? ':' : ''; 
        $holder .=  'db_prep_' . $this->prepared_counter++;
        $this->prepared_values[ $holder ] = $val;
        $this->setPreparedType( $holder, $col, $type );
        if( !static::$useColumnInBindValues ) $holder = ':'.$holder;
        return $holder;
    }

    /**
     * @param string $holder
     * @param string|null $col
     * @param string|null $type
     * @return string|null
     */
    protected function setPreparedType( $holder, $col, $type )
    {
        if( $type ) {
            $this->prepared_types[ $holder ] = $type;
        } elseif( $col && isset( $this->col_data_types[$col] ) ) {
            $this->prepared_types[ $holder ] = $this->col_data_types[ $col ];
        }
        return $type;
    }

    /**
     * @return array
     */
    public function getBinding()
    {
        return $this->prepared_values;
    }

    /**
     * @return array
     */
    public function getBindType()
    {
        return $this->prepared_types;
    }

    // +----------------------------------------------------------------------+
}