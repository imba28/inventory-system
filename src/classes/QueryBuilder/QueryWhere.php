<?php
namespace App\QueryBuilder;

use App\Exceptions\InvalidArgumentException;

class QueryWhere
{
    protected $key;
    protected $value;
    protected $operator;

    protected $table;

    private static $validOperators = array("=", "<>", "!=", "<", ">", ">=", "<=", "!<", "!>", "LIKE", "IS", "IS NOT", "OR", "AND");

    public function __construct($key, $operator, $value, $table)
    {
        if (self::isValid($key, $operator, $value)) {
            if (is_array($key)) {
                $key = new QueryWhere($key[0], $key[1], $key[2], $table);
            }
            if (is_array($value)) {
                $value = new QueryWhere($value[0], $value[1], $value[2], $table);
            }

            $this->key = $key;
            $this->operator = strtoupper($operator);
            $this->value = $value;
            $this->table = $table;

            if ($this->value instanceof \App\Model) {
                $this->value = $this->value->getId();
                if (substr($this->key, -3) != '_id') {
                    $this->key .= '_id';
                }
            }
        } else {
            throw new \InvalidArgumentException('invalid arguments');
        }
    }

    protected static function isValid($key, $operator, $value)
    {
        if (is_array($key) && count($key) != 3) {
            return false;
        }
        if (is_array($value) && count($value) != 3) {
            return false;
        }
        if (!in_array(strtoupper($operator), self::$validOperators)) {
            return false;
        }

        return true;
    }

    public function __toString()
    {
        return $this->getClause();
    }

    public function getClause()
    {
        $key = (string) $this->key;
        $value = (string) $this->value;

        if (!($this->key instanceof QueryWhere)) {
            $key = $this->sanitize($key);
        }

        if (!($this->value instanceof QueryWhere)) {
            $value = (is_null($value) || $value === 'NULL') ? 'NULL' : '?';
        }

        return "(" . join(" ", array($key, $this->operator, $value)) . ")";
    }

    public function getBindings(): array
    {
        $bindings = array();

        if ($this->key instanceof QueryWhere) {
            $bindings = array_merge($bindings, $this->key->getBindings());
        }

        if ($this->value instanceof QueryWhere) {
            $bindings = array_merge($bindings, $this->value->getBindings());
        } elseif (!is_null($this->value) && $this->value !== "NULL") {
            $bindings[] = $this->value;
        }

        return $bindings;
    }

    final private function sanitize($value)
    {
        return
        Builder::SANITIZER .
        $this->table .
        Builder::SANITIZER .
        "." .
        Builder::SANITIZER .
        $value .
        Builder::SANITIZER;
    }
}
