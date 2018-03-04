<?php
namespace App\Database;

use \App\Helper\Loggers\Logger;
use \DateTime;

/**
 * Converts values based on their database data type.
 */
class DataMapper
{
    /**
     * Contains information about all columns, such as type and name.
     *
     * @var array
     */
    private $schema;

    public function __construct(array $schema)
    {
        $this->schema = $this->parseSchema($schema);
    }

    /**
     * Converts a column value based on its type.
     *
     * @param mixed $column
     * @param mixed $value
     * @return mixed
     */
    public function map(&$column, $value)
    {
        if (isset($this->schema[$column])) {
            $columnInfo = $this->schema[$column];
            if (is_callable([$this, "get{$columnInfo['type']}"])) {
                return $this->{"get{$columnInfo['type']}"}($column, $value);
            }
        }
        return $value;
    }

    /**
     * Converts an array of column values.
     *
     * @param array $data
     * @return array
     */
    public function mapAll(array $data): array
    {
        $mapped = [];

        foreach ($data as $key => $value) {
            $column = $key;
            $mappedValue = $this->map($column, $value);
            $mapped[$column] = $mappedValue;
        }

        return $mapped;
    }

    /**
     * Parse column schema retrieved from database table description.
     *
     * @param array $schema
     * @return array
     */
    private function parseSchema(array $schema): array
    {
        $parsed = [];

        foreach ($schema as $columnInfo) {
            if (preg_match('/([\w]+)_id$/', $columnInfo['Field'], $match)) {
                $className = '\App\Models\\'.ucfirst($match[1]);
                if (class_exists($className)) {
                    $columnInfo['Type'] = 'Model';
                }
            }

            $parsed[$columnInfo['Field']] = [
                'field' => $columnInfo['Field'],
                'type' => preg_replace('/\([0-9\,\']+\)/', '', $columnInfo['Type'])
            ];
        }

        return $parsed;
    }

    /**
     * Convert a column value to string.
     *
     * @param mixed $value
     * @return string
     */
    private function getVarchar($column, $value): string
    {
        return (string) $value;
    }

    /**
     * Convert a column value to text (string).
     *
     * @param mixed $value
     * @return string
     */
    private function getText($column, $value): string
    {
        return $this->getVarchar($column, $value);
    }

    /**
     * Convert a column value to integer.
     *
     * @param mixed $value
     * @return int
     */
    private function getInt($column, $value): int
    {
        return intval($value);
    }

    /**
     * Convert a column value to enum(string)
     *
     * @todo
     * @param mixed $value
     * @return string
     */
    private function getEnum($column, $value): string
    {
        return $this->getVarchar($column, $value);
    }

    /**
     * Convert a column value to a DateTime object.
     *
     * @param mixed $value
     * @return mixed
     */
    private function getDatetime($column, $value)
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($date !== false) {
            return $date;
        } else {
            return $value;
        }
    }

    /**
     * Convert a column value to a DateTime object using timestamp format.
     *
     * @param mixed $value
     * @return mixed
     */
    private function getTimestamp($column, $value)
    {
        $date = DateTime::createFromFormat('U', $value);

        if ($date !== false) {
            return $date;
        } else {
            return $value;
        }
    }

    private function getModel(&$column, $value)
    {
        try {
            $className = "\\App\\Models\\" . ucfirst(rtrim($column, '_id'));
            $value = $className::find($value);
        } catch (\App\Exceptions\NothingFoundException $e) {
            Logger::warn("{$className} with id {$value} not found!");
            $value = null;
        }
        
        $column = rtrim($column, '_id');
        return $value;
    }
}
