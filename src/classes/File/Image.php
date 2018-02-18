<?php
namespace App\File;

class Image extends File
{
    protected static $maxFileSize = 2097152; // 2MB

    protected static $imageTypes = array (
        IMAGETYPE_GIF => "gif",
        IMAGETYPE_JPEG => "jpg",
        IMAGETYPE_PNG => "png"
    );
    protected static $imageWhitelist = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);

    /*public function getDestination() {
        return $this->file_name. "." .self::$imageTypes[exif_imagetype($this->getSource())];
    }*/

    public static function getAllowedMimes()
    {
        return array(
            'image/jpeg',
            'image/png',
            'image/gif'
        );
    }
}
