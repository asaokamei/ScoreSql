<?php
namespace WScore\ScoreSql\Paginate;

use WScore\ScoreSql\Query;

class Pager
{
    protected $pager = '_page';

    protected $limiter = '_limit';

    protected $perPage = 20;

    protected $currPage = 1;

    protected $total = null;

    protected $saveID = 'Paginated-Query';

    /**
     * @var array
     */
    protected $session = [ ];

    /**
     * @var array
     */
    protected $inputs = [ ];

    // +----------------------------------------------------------------------+
    //  construction
    // +----------------------------------------------------------------------+
    /**
     * @param array|null $session
     */
    public function __construct( &$session = null, $input = null )
    {
        if ( is_null( $session ) ) {
            $this->session = &$_SESSION;
        } else {
            $this->session = &$session;
        }
        if ( is_null( $input ) ) {
            $this->inputs = $_GET;
        } else {
            $this->inputs = $input;
        }
        $this->setSaveId();
        if ( $limit = $this->getKey( $this->limiter ) ) {
            $this->perPage = $limit;
        }
    }

    /**
     *
     */
    protected function setSaveId()
    {
        $this->saveID = 'Paginate-' . md5( $_SERVER[ "SCRIPT_FILENAME" ] );
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getKey( $key )
    {
        return array_key_exists( $key, $this->inputs ) ? $this->inputs[ $key ] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function exists( $key )
    {
        return array_key_exists( $key, $this->inputs );
    }

    // +----------------------------------------------------------------------+
    //  pagination
    // +----------------------------------------------------------------------+
    /**
     * @param \Closure $callback
     * @return mixed|Query
     */
    public function load( $callback )
    {
        if ( $this->exists( $this->pager ) ) {
            $this->loadQuery();
        }
        /** @var Query|mixed $query */
        $query = $callback( $this );
        $this->setPage( $query );
        $this->saveQuery();
        return $query;
    }

    /**
     * @param Query $query
     */
    protected function setPage( $query )
    {
        $query->limit( $this->perPage );
        $query->offset( $this->perPage * ($this->currPage - 1) );
    }

    /**
     * loads query information from saved session.
     */
    protected function loadQuery()
    {
        if( !isset( $this->session[ $this->saveID ] ) ) {
            return;
        }
        $saved        = $this->session[ $this->saveID ];
        $this->inputs = array_merge( $saved[ 'inputs' ], $this->inputs );
        $this->perPage = $saved[ 'perPage' ];
        if( $currPage=$this->getKey($this->pager) ) {
            $this->currPage = $currPage;
        } else{
            $this->currPage = $saved['currPage'];
        }
    }

    /**
     * saves query information into session.
     */
    protected function saveQuery()
    {
        $this->session[ $this->saveID ] = [
            'perPage'  => $this->perPage,
            'currPage' => $this->currPage,
            'inputs'   => $this->inputs,
        ];
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function push( $key, $default='' )
    {
        if( !array_key_exists( $key, $this->inputs ) ) {
            $this->inputs[$key] = $default;
        }
        return $this->inputs[$key];
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->inputs;
    }

    /**
     * @param int $total
     */
    public function setTotal( $total )
    {
        $this->total = $total;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage( $perPage )
    {
        $this->perPage = $perPage;
    }

    // +----------------------------------------------------------------------+
    //  public methods for constructing pagination info.
    // +----------------------------------------------------------------------+
    /**
     * @param string $uri
     * @return ToHtml
     */
    public function html( $uri=null )
    {
        $toHtml = new ToHtml( $uri );
        $toHtml->setPager($this);
        return $toHtml;
    }

    /**
     * @return int
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCurrPage() {
        return $this->currPage;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return string
     */
    public function getPageKey()
    {
        return $this->pager;
    }

    // +----------------------------------------------------------------------+
}