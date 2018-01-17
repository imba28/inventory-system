<?php
namespace App\File;

class Image extends File {
    public static $max_file_size = 1048576; // 10MB

    protected static $image_types = array (
        IMAGETYPE_GIF => "gif",
        IMAGETYPE_JPEG => "jpg",
        IMAGETYPE_PNG => "png"
    );
    protected static $image_whitelist = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);

    /*public function getDestination() {
        return $this->file_name. "." .self::$image_types[exif_imagetype($this->getSource())];
    }*/

    public static function getAllowedMimes() {
        return array(
            'image/jpeg',
            'image/png',
            'image/gif'
        );
    }
}
class InvalidImageExecption extends \Exception {}
?>