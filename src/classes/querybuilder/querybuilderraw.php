<?php
namespace App\QueryBuilder;

class QueryBuilderRaw extends App\QueryBuilder {
    protected $sql;

    public function __construct($sql, $debug) {
        $this->sql = $sql;
        $this->debug = $debug;
    }

    public function get() {
        $this->sql = preg_replace('/(__PREFIX__)/', db_table_name(''), $this->sql);

        if($this->debug) {
            vd($this->sql);
        }

        return $this->query($this->sql, array());
    }

    public function setQuery($sql) {
        $this->sql = $sql;
        return $this;
    }
}
?>
