<?php
namespace App\QueryBuilder;

class SQLiteBuilder extends Builder
{
    protected static $sqlKeywords = array("DATETIME('NOW')", "COUNT(*)", "CURRENT_DATE", "CURRENT_USER", "DEFAULT", "CURRENT_TIMESTAMP");

    /**
     * getInsertStatement
     *
     * @todo this is bad!!!!
     * @param mixed $data
     * @return void
     */
    protected function getInsertStatement($data)
    {
        foreach ($data as $key => $value) {
            if ($value === 'NOW()') {
                $data[$key] = 'DATETIME(\'now\')';
            }
        }

        return parent::getInsertStatement($data);
    }
}