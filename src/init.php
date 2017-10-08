<?php
require_once('lib/functions.php');

use App;

\App\Configuration::set('DB_HOST', '127.0.0.1');
\App\Configuration::set('DB_DB', 'av');
\App\Configuration::set('DB_PORT', '3306');
\App\Configuration::set('DB_USER', 'root');
\App\Configuration::set('DB_PWD', 'keins');
\App\Configuration::set('DB_PREFIX', 'av');

try {
    \App\Registry::setDatabase(new \App\Database());
}
catch(Exception $e){
    die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
}
?>