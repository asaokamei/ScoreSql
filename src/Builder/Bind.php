<?php
namespace WScore\SqlBuilder\Builder;

class Bind
{
    /**
     * @var bool
     */
    static $useColumnInBindValues = false;
    
    /**
     * @var int
     */
    protected static $prepared_counter = 1;

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

    /**
     * reset the counter to 1. 
     */
    public static function reset() {
        static::$prepared_counter = 1;
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
     * @param null|int     $type    data type
     * @param null $col     column name. used to find data type
     * @return string|array
     */
    public function prepare( $val, $type=null, $col=null )
    {
        if( is_array( $val ) ) {
            $holder = [];
            foreach( $val as $key => $v ) {
                $holder[$key] = $this->prepare( $v, $type, $col );
            }
            return $holder;
        }
        if( is_callable( $val ) ) return $val;

        $holder  = ( static::$useColumnInBindValues ) ? ':' : ''; 
        $holder .=  'db_prep_' . static::$prepared_counter++;
        $this->prepared_values[ $holder ] = $val;
        if( $type ) {
            $this->prepared_types[ $holder ] = $type;
        }
        elseif( $col && array_key_exists( $col, $this->col_data_types ) ) {
            $this->prepared_types[ $holder ] = $this->col_data_types[ $col ];
        }
        $holder  = ( ( static::$useColumnInBindValues ) ? '' : ':' ) . $holder;
        return $holder;
    }

    /**
     * @return array
     */
    public function getBinding()
    {
        return $this->prepared_values;
    }

    /**
     * @param Bind $bind
     */
    public function mergeBinding( $bind )
    {
        $this->prepared_values = array_merge( $this->prepared_values, $bind->getBinding() );
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