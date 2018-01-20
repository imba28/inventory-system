<?php
namespace App;

class ClassManager {
    public static $notExistingClasses = array();

    public static function markNotExisting($class) {
        if(!in_array($class, self::$notExistingClasses)) {
            self::$notExistingClasses[] = $class;
        }
    }

    public static function markedNotExisting($class) {
        return in_array($class, self::$notExistingClasses);
    }
}
?>