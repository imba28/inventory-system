<?php
session_start();

require_once('lib/functions.php');

$loader = require(ABS_PATH . '/vendor/autoload.php');
$loader->unregister();

spl_autoload_register(function ($class) use ($loader) {
    $_class = preg_replace('/^(app)\\\/i', '', $class);
    $_class = str_replace('\\', '/', strtolower($_class));

    if(file_exists(ABS_PATH . "/src/classes/{$_class}.php")) {
        require_once(ABS_PATH . "/src/classes/{$_class}.php");
    }
    else {
        $file = $loader->findFile($class);

        if ($file) {
            \Composer\Autoload\includeFile($file);
            $file = realpath($file);

            $classBits = explode('\\', $class);
            $pathBits = explode('/', preg_replace('{\.(hh|php)$}', '', strtr($file, '\\', '/')));
            for ($i = count($classBits)-1, $j = count($pathBits)-1; $i >= 0 && $j >= 0; $i--, $j--) {
                if (strtolower($classBits[$i]) !== strtolower($pathBits[$j])) {
                    break;
                }
                if ($classBits[$i] !== $pathBits[$j]) {
                    throw new \Exception('Class/path case mismatch for '.$class.' found in '.$file);
                }
            }

            return true;
        }
    }
});

require_once(ABS_PATH . '/src/config/config.php');

if(App\Configuration::get('env') === 'dev') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

$router = App\Router::getInstance();
include(ABS_PATH . '/src/config/routes.php');

\App\Menu::getInstance()->set('items', array(
    'Produkte' => 'products',
    'Kunden' => 'customers',
    'Inventur' => 'inventur'
));

try {
    \App\Registry::setDatabase(new \App\Database());
}
catch(Exception $e){
    die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
}
?>