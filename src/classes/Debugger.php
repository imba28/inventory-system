<?php
namespace App;

class Debugger {
    public static function log($message, $type = 'info') {
        $logObj = Models\log::new();

        $logObj->set('message', $message);
        $logObj->set('type', $type);

        $logObj->save();
    }
}
?>