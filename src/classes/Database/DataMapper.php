<?php
namespace App\Database;

use \App\Helper\Loggers\Logger;
use \App\Models\Model;
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
     * Converts a database column value to an application value based on its data type.
     *
     * @param mixed $column
     * @param mixed $value
     * @return mixed
     */
    public function mapTo(&$column, $value)
    {
        if (isset($this->schema[$column])) {
            $columnInfo = $this->schema[$column];
            if (is_callable([$this, "to{$columnInfo['type']}"])) {
                return $this->{"to{$columnInfo['type']}"}($column, $value);
            }
        }
        return $value;
    }

    /**
     * Converts array of application values to database values.
     *
     * @param array $data
     * @return array
     */
    public function mapToAll(array $data): array
    {
        $mapped = [];

        foreach ($data as $key => $value) {
            $column = $key;
            $mappedValue = $this->mapTo($column, $value);
            $mapped[$column] = $mappedValue;
        }

        return $mapped;
    }

    public function mapFrom(&$column, $value)
    {
        if ($value instanceof Model) {
            $column .= '_id';
        }

        if (isset($this->schema[$column])) {
            $columnInfo = $this->schema[$column];
            if (is_callable([$this, "from{$columnInfo['type']}"])) {
                return $this->{"from{$columnInfo['type']}"}($column, $value);
            }
        }

        if (is_null($value) || strlen(trim($value)) === 0) {
            return null;
        }

        return $value;
    }

    /**
     * Converts array of application values to database values.
     *
     * @param array $data
     * @return void
     */
    public function mapFromAll(array $data): array
    {
        $mapped = [];

        foreach ($data as $key => $value) {
            $column = $key;
            $mappedValue = $this->mapFrom($column, $value);
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
    private function toVarchar($column, $value): string
    {
        return (string) $value;
    }

    /**
     * Convert a column value to text (string).
     *
     * @param mixed $value
     * @return string
     */
    private function toText($column, $value): string
    {
        return $this->toVarchar($column, $value);
    }

    /**
     * Convert a column value to integer.
     *
     * @param mixed $value
     * @return int
     */
    private function toInt($column, $value): int
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
    private function toEnum($column, $value): string
    {
        return $this->toVarchar($column, $value);
    }

    /**
     * Convert a column value to a DateTime object.
     *
     * @param mixed $value
     * @return mixed
     */
    private function toDatetime($column, $value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            try {
                $date = new DateTime($value);
            } catch (\Exception $e) {
                return $value;
            }
            
            if ($date !== false) {
                return $date;
            }
        }
        
        return $value;
    }

    private function toDate($column, $value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $date = DateTime::createFromFormat('d.m.Y', $value);

            if ($date !== false) {
                return $date;
            }
        }
        
        return $value;
    }

    /**
     * Convert a column value to a DateTime object using timestamp format.
     *
     * @param mixed $value
     * @return mixed
     */
    private function toTimestamp($column, $value)
    {
        if (is_null($value)) {
            return null;
        }
        
        if (is_string($value)) {
            $date = DateTime::createFromFormat('U', $value);

            if ($date !== false) {
                return $date;
            }
        }
        return $value;
    }

    /**
     * Converts a column value to a Models\Model object. Changes column name, which is passed by reference.
     *
     * @see \App\Models\Model
     * @param mixed &$column
     * @param mixed $value
     * @return mixed
     */
    private function toModel(&$column, $value)
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

    private function fromDate($column, DateTime $value): string
    {
        return $value->format('Y-m-d');
    }

    private function fromDatetime($column, DateTime $value): string
    {
        return $value->format('Y-m-d H:i:s');
    }

    private function fromTimestamp($column, DateTime $value): int
    {
        return $value->format('U');
    }

    private function fromModel(&$column, Model $value): string
    {
        // TODO add a method that indicates whether or not a model is saved.
        if (!$value->isCreated()) {
            $value->save();
        }
        
        return $value->getId();
    }
}
