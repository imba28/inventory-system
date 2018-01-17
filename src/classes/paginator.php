<?php
namespace App;

class Paginator {
    private $query;

    private $totals;
    private $currentPage;
    private $itemsPerPage;
    private $linkRoot;

    public function __construct(QueryBuilder\Builder $query, $currentPage, $itemsPerPage, $linkRoot) {
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->query = $query;
        $this->linkRoot = $linkRoot;

        $this->totals = $query->reset('limit')->count();
    }

    public function getTotals() {
        return $this->totals;
    }

    public function render($list_class = 'pagination') {
        if ( $this->itemsPerPage == 'all' ) {
            return '';
        }

        $last       = ceil( $this->totals / $this->itemsPerPage );
        $start      = ($this->currentPage > 0 ) ? 1 : 1;
        $end        = ($this->currentPage > $last ) ? $this->currentPage : $last;

        $html       = '<ul class="' . $list_class . ' mt-4">';

        $class      = ( $this->currentPage == 1 ) ? "disabled" : "";
        $html       .= '<li class="' . $class . ' page-item"><a class="page-link" href="'.$this->linkRoot.'/' . ( $this->currentPage - 1 ) . '">&laquo;</a></li>';

        if ( $start > 1 ) {
            $html   .= '<li class="page-item"><a href="'.$this->linkRoot.'/1">1</a></li>';
        }

        for ( $i = $start ; $i <= $end; $i++ ) {
            $class  = ( $this->currentPage == $i ) ? "active" : "";
            $html   .= '<li class="' . $class . ' page-item"><a class="page-link" href="'.$this->linkRoot.'/' . $i . '">' . $i . '</a></li>';
        }

        if ( $end < $last ) {
            $html   .= '<li class="page-item"><a class="page-link" href="'.$this->linkRoot.'/' . $last . '">' . $last . '</a></li>';
        }

        $class      = ( $this->currentPage == $last ) ? "disabled" : "";
        $html       .= '<li class="' . $class . ' page-item"><a class="page-link" href="'.$this->linkRoot.'/' . ( $this->currentPage + 1 ) . '">&raquo;</a></li>';

        $html       .= '</ul>';

        return $html;
    }
}
?>