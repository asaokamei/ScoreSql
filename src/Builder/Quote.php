<?php
namespace WScore\SqlBuilder\Builder;

use WScore\SqlBuilder\Sql\Sql;

class Quote
{
    /**
     * @var Sql
     */
    protected $format = '"%s"';

    protected $quote = '"';

    /**
     * @param string $q
     */
    public function setQuote( $q ) {
        $this->quote = $q;
        $this->format = $q . '%s' . $q;
    }

    /**
     * @param array $list
     * @return array
     */
    public function map( $list )
    {
        $list = array_map( function($val) {
            return $this->quote($val);
        }, $list );
        return $list;
    }
    
    /**
     * @param string $name
     * @param array|string $separator
     * @return string
     */
    public function quote( $name, $separator=[' AS ', ' as ', '.'] )
    {
        if( !$name ) return $name;
        if( is_callable( $name ) ) return $name();
        if( !$separator ) return $this->quoteString( $name );
        if( !is_array( $separator ) ) $separator = array($separator);
        while( $sep = array_shift( $separator ) ) {
            if( false !== stripos( $name, $sep ) ) {
                $list = explode( $sep, $name );
                foreach( $list as $key => $str ) {
                    $list[$key] = $this->quote( $str, $separator );
                }
                return implode( $sep, $list );
                break;
            }
        }
        return $this->quoteString($name);
    }

    /**
     * @param $name
     * @return string
     */
    public function quoteString( $name )
    {
        if( !$name ) return $name;
        if( $name == '*' ) return $name;
        if( substr( $name, 0, 1 ) == $this->quote && 
            substr( $name, -1 ) == $this->quote ) {
            return $name;
        }
        return sprintf( $this->format, $name );
    }
}