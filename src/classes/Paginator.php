<?php
namespace App;

class Paginator
{
    private $totals;
    private $currentPage;
    private $itemsPerPage;
    private $linkRoot;

    public function __construct($totalItems, $currentPage, $itemsPerPage, $linkRoot)
    {
        $this->totals = $totalItems;
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;

        $this->linkRoot = $linkRoot;
    }

    public function getTotals()
    {
        return $this->totals;
    }

    public function render($listClass = 'pagination')
    {
        if ($this->itemsPerPage == 'all') {
            return '';
        }

        $last = ceil($this->totals / $this->itemsPerPage);
        $start = 1;
        $end = ($this->currentPage > $last ) ? $this->currentPage : $last;

        $html = "<ul class='$listClass mt-4 justify-content-center'>";

        if ($this->currentPage === 1) {
            $html .= "
            <li class='disabled page-item'>
                <a href='javascript:void(0)' class='page-link'>&laquo;</a>
            </li>";
        } else {
            $html .= "
            <li class='page-item'>
                <a class='page-link' href='$this->linkRoot/". ($this->currentPage - 1) ."'>&laquo;</a>
            </li>";
        }
        $class = ( $this->currentPage == 1 ) ? 'disabled' : '';


        if ($start > 1) {
            $html .= "
            <li class='page-item'>
                <a href='$this->linkRoot/1'>1</a>
            </li>";
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ( $this->currentPage == $i ) ? "active" : "";
            $html.=  "
            <li class='$class page-item'>
                <a class='page-link' href='$this->linkRoot/$i'>$i</a>
            </li>";
        }

        if ($end < $last) {
            $html .= "
            <li class='page-item'>
                <a class='page-link' href='$this->linkRoot/$last'>$last</a>
            </li>";
        }

        $class = ( $this->currentPage == $last ) ? 'disabled' : '';
        $html .= "
        <li class='$class page-item'>
            <a class='page-link' href='$this->linkRoot/" . ( $this->currentPage + 1 ) . "'>&raquo;</a>
        </li>";

        $html .= '</ul>';

        return $html;
    }
}
