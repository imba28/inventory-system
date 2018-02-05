<?php
namespace App;

class Format {
    private $formatClosures;

    public function __construct() {
        $this->formatClosures = array_fill_keys(array('html', 'json', 'xml'), true);
    }

    public function __call($formatType, array $arguments) {
        if(isset($this->formatClosures[$formatType])) {
            $this->formatClosures[$formatType] = $arguments[0];
        }
        else throw new Exceptions\InvalidOperationException("Method `{$formatType}` does not exist!");
    }

    public function execute($formatType) {
        if(isset($this->formatClosures[$formatType])) {
            if($this->formatClosures[$formatType] instanceof \Closure) {
                return $this->formatClosures[$formatType]();
            }
        }
        else throw new \InvalidArgumentException("Format type `{$formatType}` does not exist! Use one of these: " . join(array_keys($this->formatClosures), ', '));
    }
}
?>