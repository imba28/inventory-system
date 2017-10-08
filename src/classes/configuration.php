<?php
namespace App;

class Configuration {
    private static $data;

    public static function set($property, $value) {
        self::$data[$property] = $value;
    }
    public static function get($property) {
        return isset(self::$data[$property]) ? self::$data[$property] : null;
    }
}
?>
