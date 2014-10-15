<?php
namespace WScore\ScoreSql\Paginate;

class ToHtml
{
    /**
     * @var Pager
     */
    protected $pager;

    /**
     * @var int
     */
    protected $currPage;

    /**
     * @var string
     */
    protected $currUri = null;

    /**
     * @param string $uri
     */
    public function __construct( $uri=null )
    {
        $this->currUri = $uri ?: $this->getRequestUri();
    }

    /**
     * @return string
     */
    protected function getRequestUri()
    {
        $uri = $_SERVER[ 'REQUEST_URI' ];
        $uri = htmlspecialchars( $uri, ENT_QUOTES, 'UTF-8' );
        if( ( $post = strpos( $uri, '?' ) ) !== false ) {
            $uri = substr( $uri, 0, $post );
        }
        return $uri;
    }

    /**
     * @param Pager $pager
     */
    public function setPager( $pager )
    {
        $this->pager = $pager;
    }

    // +----------------------------------------------------------------------+
    //  preparing for pagination list. Yep, this should go any other class.
    // +----------------------------------------------------------------------+
    /**
     * @param int $numLinks
     * @return string
     */
    function bootPages( $numLinks=5 )
    {
        $html = '';
        $pages = $this->getPagination($numLinks);
        $html .= $this->bootLi( '&laquo;', $pages['top_page'] );
        $html .= $this->bootLi( 'prev', $pages['prev_page'] );
        foreach( $pages['page'] as $page ) {
            $html .= $this->bootLi( $page, $page, 'active' );
        }
        $html .= $this->bootLi( 'next', $pages['next_page'] );
        $html .= $this->bootLi( '&raquo;', $pages['last_page'] );
        return "<ul class=\"pagination\">{$html}</ul>";
    }

    /**
     * @param string $label
     * @param int    $page
     * @param string $type
     * @return string
     */
    protected function bootLi( $label, $page, $type='disable' )
    {
        if( $page != $this->currPage ) {
            $key = $this->pager->getPageKey();
            $html = "<li><a href='{$this->currUri}?{$key}={$page}' >{$label}</a></li>";
        } elseif( $type == 'disable' ) {
            $html = "<li class='disabled'><a href='#' >{$label}</a></li>";
        } else {
            $html = "<li class='active'><a href='#' >{$label}</a></li>";
        }
        return $html;
    }

    /**
     * @param int $numLinks
     * @return array
     */
    function getPagination( $numLinks = 5 )
    {
        $this->currPage = $this->pager->getCurrPage();
        $pages = [
            'found' => $this->pager->getTotal(),
            'curr_page' => $this->currPage,
        ];
        $pages[ 'top_page'  ] = 1;
        $pages[ 'last_page' ] = $lastPage = $this->findLastPage($numLinks);

        // prepare pages
        $pages['page'] = $this->fillPages($numLinks);

        // previous and next pages.
        $pages['prev_page'] = $this->currPage>1 ? $this->currPage-1: 1;
        $pages['next_page'] = $this->currPage<$lastPage ? $this->currPage+1: $lastPage;
        return $pages;
    }

    /**
     * @param $numLinks
     * @return array
     */
    protected function fillPages($numLinks)
    {
        $start    = $this->findStart($numLinks);
        $last     = $this->findLast( $numLinks );

        $pages    = [];
        for( $page = $start; $page <= $last; $page++ ) {
            $pages[] = $page;
        }
        return $pages;
    }

    /**
     * @param int $numLinks
     * @return int
     */
    protected function findStart($numLinks)
    {
        $start = $this->currPage - $numLinks;
        return $start >= 1 ? $start: 1;
    }

    /**
     * @param int $numLinks
     * @return int
     */
    protected function findLastPage($numLinks)
    {
        // total and perPage is set.
        $total = $this->pager->getTotal();
        $pages = $this->pager->getPerPage();
        if( !is_null( $total ) && $pages ) {
            return (integer) ( ceil( $total / $pages ) );
        }
        return $this->currPage + $numLinks;
    }

    /**
     * @param int $numLinks
     * @return int
     */
    protected function findLast($numLinks)
    {
        $lastPage = $this->findLastPage($numLinks);
        $last = $this->currPage + $numLinks;
        if( $last <= $lastPage ) {
            return $last;
        }
        return $lastPage;
    }
    // +----------------------------------------------------------------------+
}
