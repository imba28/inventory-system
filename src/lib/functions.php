<?php
function __autoload($class) {
    $temp = $class;
    $class = preg_replace('/^(app)\\\/i', '', $class);
    $class = str_replace('\\', '/', strtolower($class));

    if(file_exists(ABS_PATH . "/src/{$class}.php")) {
        require_once(ABS_PATH . "/src/{$class}.php");
    }
    elseif(file_exists(ABS_PATH . "/src/classes/{$class}.php")) {
        require_once(ABS_PATH . "/src/classes/{$class}.php");
    }
    else {
        //trigger_error("Class {$temp} not found!", E_USER_WARNING);
        \App\ClassManager::markNotExisting("\\{$temp}");

        /*
        vd("Klasse {$class}:");
        vd(generateCallTrace());
        vd(ABS_PATH . "/src/classes/{$class}.php");
        vd(ABS_PATH . "/src/{$class}.php");
        */

        return false;
        #throw new Exception("Die Klasse `$class` wurde nicht gefunden!");
    }
}

function vd($a) {
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}

function getTableName(string $table) {
    return \App\Configuration::get('DB_PREFIX') . "_{$table}";
}

function generateCallTrace() { // author: http://php.net/manual/de/function.debug-backtrace.php#112238
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();

    for ($i = 0; $i < $length; $i++) {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }

    return "\t" . implode("\n\t", $result);
}
?>