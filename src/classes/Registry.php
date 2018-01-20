<?php
namespace App;

class Registry {
    use Traits\Singleton;

    protected $data = array();

    const KEY_CONFIG = "config";
    const KEY_DATABASE = "database";

    public static function set($key, $value) {
        $reg = self::getInstance();
        $reg->data[$key] = $value;
    }

    public static function get($key) {
        $reg = self::getInstance();
        if(isset($reg->data[$key])) return $reg->data[$key];
        return null;
    }

    public static function setConfig(Configuration $config) {
        $reg = self::getInstance();
        $reg->data[self::KEY_CONFIG] = $config;
    }

    public static function setDatabase(Database $db) {
        $reg = self::getInstance();
        $reg->data[self::KEY_DATABASE] = $db;
    }

    public static function getConfig() {
        return self::get(self::KEY_CONFIG);
    }

    public static function getDatabase() {
        return self::get(self::KEY_DATABASE);
    }
}
?>