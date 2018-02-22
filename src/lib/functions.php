<?php
/*function __autoload($class) {
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
        /*
        return false;
        #throw new Exception("Die Klasse `$class` wurde nicht gefunden!");
    }
}*/

function vd($a)
{
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}

function getTableName(string $table)
{
    return \App\Configuration::get('DB_PREFIX') . "_{$table}";
}

function generateCallTrace()
{
    // author: http://php.net/manual/de/function.debug-backtrace.php#112238
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();

    for ($i = 0; $i < $length; $i++) {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' '));
        // replace '#someNum' with '$i)', set the right ordering
    }

    return "\t" . implode("\n\t", $result);
}

function array_find(array &$xs, callable $f)
{
    foreach ($xs as $key => $x) {
        if (call_user_func($f, $x) === true) {
            return $xs[$key];
        }
    }
    return null;
}

function ago($time)
{
    if ($time instanceof DateTime) {
        $time = $time->getTimestamp();
    } else {
        if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $time)) {
            $time = DateTime::createFromFormat("Y-m-d", $time)->getTimestamp();
        } elseif (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $time)) {
            $time = DateTime::createFromFormat("Y-m-d H:i:s", $time)->getTimestamp();
        } elseif (preg_match("/^[0-9]+$/", $time)) {
            $time = DateTime::createFromFormat("U", $time)->getTimestamp();
        } else {
            throw new InvalidArgumentException("$time is not a valid time string!");
        }
    }

    $periods = array("Sekunde", "Minute", "Stunde", "Tag", "Woche", "Monat", "Jahr");
    $lengths = array("60","60","24","7","4.35","12","10");

    $now = time();

    $difference = $now - $time;

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if ($difference != 1) {
        switch ($j) {
            case 0:
            case 1:
            case 2:
            case 4:
                $periods[$j].= "n";
                break;
            case 3:
            case 5:
            case 6:
                $periods[$j].= "en";
                break;
        }
    }

    return "$difference $periods[$j]";
}

function fileext_to_mime($filename)
{
       $mime_types = array(
           'txt' => 'text/plain',
           'htm' => 'text/html',
           'html' => 'text/html',
           'php' => 'text/html',
           'css' => 'text/css',
           'js' => 'application/javascript',
           'json' => 'application/json',
           'xml' => 'application/xml',
           'swf' => 'application/x-shockwave-flash',
           'flv' => 'video/x-flv',

           // images
           'png' => 'image/png',
           'jpe' => 'image/jpeg',
           'jpeg' => 'image/jpeg',
           'jpg' => 'image/jpeg',
           'gif' => 'image/gif',
           'bmp' => 'image/bmp',
           'ico' => 'image/vnd.microsoft.icon',
           'tiff' => 'image/tiff',
           'tif' => 'image/tiff',
           'svg' => 'image/svg+xml',
           'svgz' => 'image/svg+xml',

           // archives
           'zip' => 'application/zip',
           'rar' => 'application/x-rar-compressed',
           'exe' => 'application/x-msdownload',
           'msi' => 'application/x-msdownload',
           'cab' => 'application/vnd.ms-cab-compressed',

           // audio/video
           'mp3' => 'audio/mpeg',
           'qt' => 'video/quicktime',
           'mov' => 'video/quicktime',

           // adobe
           'pdf' => 'application/pdf',
           'psd' => 'image/vnd.adobe.photoshop',
           'ai' => 'application/postscript',
           'eps' => 'application/postscript',
           'ps' => 'application/postscript',

           // ms office
           'doc' => 'application/msword',
           'rtf' => 'application/rtf',
           'xls' => 'application/vnd.ms-excel',
           'ppt' => 'application/vnd.ms-powerpoint',

           // open office
           'odt' => 'application/vnd.oasis.opendocument.text',
           'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
       );

       $split = explode('.', $filename);
       $ext = strtolower(array_pop($split));
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

function tryParseDate($string)
{
    if (strtoupper($string) === 'NOW' || strtolower($string)  === 'NOW()') {
        $date = new DateTime('now');
    } elseif (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $string)) { // YYYY-MM-DD
        $date = DateTime::createFromFormat("Y-m-d", $string);
    } elseif (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $string)) { // YYYY-MM-DD HH:ii:ss
        $date = DateTime::createFromFormat("Y-m-d H:i:s", $string);
    } elseif (preg_match("/^[0-9]{2}.[0-9]{2}.[0-9]{4}$/", $string)) { // DD.MM.YYYY
        $date = DateTime::createFromFormat("d.m.Y", $string);
    } elseif (preg_match("/^[0-9]+$/", $string)) { // unix timestamp
        $date = DateTime::createFromFormat("U", $string);
    } else {
        return null;
    }

    return $date;
}
