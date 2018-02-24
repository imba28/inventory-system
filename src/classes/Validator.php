<?php
namespace App;

/**
 * Takes rules and validates data.
 */
class Validator
{
    protected $rules;
    protected $data;

    protected $errors;

    public function __construct(array $rules, array $data)
    {
        $this->rules = $this->parseRules($rules);
        $this->data = $this->parseData($data);

        $this->errors = array();
    }

    /**
     * checks if all validations pass
     *
     * @return bool
     */
    public function passes(): bool
    {
        $this->errors = array();
       
        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $validate) {
                $value = $this->data[$attribute] ?? null;
                $validate($value, $attribute);
            }
        }

        return count($this->errors) == 0;
    }

    /**
     * checks if validations fail
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * sets data, allows applying the validator on multiple datasets.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $this->parseData($data);
    }

    /**
     * returns list of errors
     *
     * @todo   add messages
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns an array of fields that failed their tests.
     *
     * @return array
     */
    public function getFailedFields(): array
    {
        $fields = [];
        foreach ($this->errors as $errors) {
            foreach ($errors as $attribute) {
                $fields[] = $attribute['field'];
            }
        }

        return $fields;
    }
    /**
     * converts array of rulesets into array of closures, that validate data.
     *
     * @param array $rulesArray
     * @return array
     */
    protected function parseRules(array $rulesArray): array
    {
        $rulesArray = $this->explodeRules($rulesArray);
        
        $ruleSet = [];
        foreach ($rulesArray as $name => $rules) {
            if (!isset($ruleSet[$name])) {
                $ruleSet[$name] = [];
            }

            foreach ($rules as $rule) {
                $ruleSet[$name][] = $this->getRule($rule);
            }
        }

        return $ruleSet;
    }

    protected function parseData(array $data)
    {
        return $data;
    }

    /**
     * splits array of rulessets by separating the strings into substrings, using the pipe symbol "|" as a seperator
     *
     * @param array $rulesArray
     * @return array
     */
    protected function explodeRules(array $rulesArray): array
    {
        foreach ($rulesArray as $name => $rules) {
            $rulesArray[$name] = (is_string($rules)) ? explode('|', $rules) : $rules;
        }

        return $rulesArray;
    }

    /**
     * returns a closure, which determines whether or not an attribute value is valid.
     *
     * @param  mixed $string
     * @throws \Exception
     * @return \Closure
     */
    protected function getRule($string)
    {
        $string = explode(':', $string, 2);
        $rule = $string[0];

        $validatorMethod = "validate{$rule}";

        if (!(is_callable([$this, $validatorMethod], false))) {
            throw new \Exception("{$rule} is not a valid validator!");
        }

        array_shift($string);
        $param = current($string);

        return function ($value, $field) use ($validatorMethod, $param, $rule) {
            $valid = call_user_func_array(array($this, $validatorMethod), [$value, $param]);

            if ($valid === false) {
                $this->errors[$rule][] = array(
                    'field' => $field,
                    'value' => $value,
                    'param' => $param
                );
            }

            return $valid;
        };
    }

    /**
     * check if an attribute is set, not null and not empty.
     *
     * @param  mixed $value
     * @return bool
     */
    protected function validateRequired($value): bool
    {
        if (is_null($value)) {
            return false;
        }
        if (is_string($value) && strlen(trim($value)) <= 0) {
            return false;
        }
        if (is_array($value) && count($value) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * checks if an attribute is a valid date string
     *
     * @param  mixed $value
     * @return bool
     */
    protected function validateDate($value): bool
    {
        $date = new Date($value);

        return $date !== false;
    }

    /**
     * checks if an attribute is null
     *
     * @param  mixed $value
     * @return bool
     */
    protected function validateMustBeNull($value): bool
    {
        return is_null($value);
    }

    /**
     * checks if an attribute is a valid email adress
     *
     * @param  mixed $value
     * @return bool
     */
    protected function validateEmail($value): bool
    {
        if (!$this->validateRequired($value)) { // if value is not set
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * checks if an attribute is smaller than/equal to a threshold.
     *
     * @param  mixed $value
     * @param  mixed $limit
     * @return bool
     */
    protected function validateMax($value, $limit): bool
    {
        return strlen($value) <= $limit;
    }

    /**
     *  checks if an attribute is greater than/equal to a threshold.
     *
     * @param  mixed $value
     * @param  mixed $limit
     * @return void
     */
    protected function validateMin($value, $limit): bool
    {
        return strlen($value) >= $limit;
    }
}
