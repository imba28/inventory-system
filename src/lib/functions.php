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

function array_find(array &$xs, callable $f) {
    foreach ($xs as $key => $x) {
        if (call_user_func($f, $x) === true) return $xs[$key];
    }
    return null;
}

function ago($time) {
    if($time instanceof DateTime) {
        $time = $time->getTimestamp();
    }
    else {
        if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $time)) {
            $time = DateTime::createFromFormat("Y-m-d", $time)->getTimestamp();
        }
        elseif(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $time)) {
            $time = DateTime::createFromFormat("Y-m-d H:i:s", $time)->getTimestamp();
        }
        elseif(preg_match("/^[0-9]+$/", $time)) {
            $time = DateTime::createFromFormat("U", $time)->getTimestamp();
        }
        else {
            throw new InvalidArgumentException("$time is not a valid time string!");
        }
    }

    $periods = array("Sekunde", "Minute", "Stunde", "Tag", "Woche", "Monat", "Jahr");
    $lengths = array("60","60","24","7","4.35","12","10");

    $now = time();

    $difference = $now - $time;

    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if($difference != 1) {
        switch($j) {
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

function mime2ext($mime) {
    $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
    $all_mimes = json_decode($all_mimes,true);
    foreach ($all_mimes as $key => $value) {
        if(array_search($mime,$value) !== false) return $key;
    }
    return false;
}

function fileext_to_mime($filename) {
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

       $split = explode('.',$filename);
       $ext = strtolower(array_pop($split));
       if (array_key_exists($ext, $mime_types)) {
           return $mime_types[$ext];
       }
       elseif (function_exists('finfo_open')) {
           $finfo = finfo_open(FILEINFO_MIME);
           $mimetype = finfo_file($finfo, $filename);
           finfo_close($finfo);
           return $mimetype;
       }
       else {
           return 'application/octet-stream';
       }
   }

   function tryParseDate($string) {
       if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $string)) { // YYYY-MM-DD
           $date = DateTime::createFromFormat("Y-m-d", $string);
       }
       elseif(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $string)) { // YYYY-MM-DD HH:ii:ss
           $date = DateTime::createFromFormat("Y-m-d H:i:s", $string);
       }
       elseif(preg_match("/^[0-9]{2}.[0-9]{2}.[0-9]{4}$/", $string)) { // DD.MM.YYYY
           $date = DateTime::createFromFormat("d.m.Y", $string);
       }
       elseif(preg_match("/^[0-9]+$/", $string)) { // unix timestamp
           $date = DateTime::createFromFormat("U", $string);
       }
       else {
           return null;
       }

       return $date;
   }
?>