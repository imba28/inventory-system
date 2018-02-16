<?php
namespace App\File;

class File
{
    public static $maxFileSize = 1875; // 15kb

    protected $fileInfo;

    protected $fileName;
    protected $fileExt;

    protected $error;

    private $isValid = false;

    public function __construct($fileInfo)
    {
        $this->fileInfo = $fileInfo;

        $fileParts = explode(".", $this->fileInfo["name"]);
        $this->fileName = sha1($fileParts[0] . time() . rand(0, 10));
    }

    public function isValid()
    {
        try {
            if (!isset($this->fileInfo['error']) || is_array($this->fileInfo['error'])) {
                throw new \RuntimeException('Ungültige Bildparameter!');
            }

            switch ($this->fileInfo['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new NoFileSentException('Es wurde keine Datei gesendet!');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new \RuntimeException('Datei ist zu groß!');
                default:
                    throw new \RuntimeException('Unbekannter Fehler während des Uploads!');
            }

            if ($this->fileInfo['error'] > 0) {
                throw new \RuntimeException("Datei ist beschädigt, da während des Upload ein Fehler aufgetreten ist!");
            }

            if ($this->fileInfo['size'] > 83886080) { // 10MB
                throw new \RuntimeException('Datei ist zu groß!');
            }

            $allowedMimeTypes = self::getAllowedMimes();

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimetype_idx = array_search(
                $finfo->file($this->fileInfo['tmp_name']),
                $allowedMimeTypes,
                true
            );
            if ($mimetype_idx === false) {
                throw new \RuntimeException(
                    "`{$finfo->file($this->fileInfo['tmp_name'])}` ist kein erlaubtes Dateiformat!"
                );
            }

            $mime_type = $allowedMimeTypes[$mimetype_idx];
            $this->file_ext = mime2ext($mime_type);
            //if($this->file_ext != "html") throw new Execption("Fehler beim Upload des Bildes!");

            $this->isValid = true;
            return true;
        } catch (\Exception $e) {
            $this->error = $e;
            return false;
        }
    }

    public function getSource()
    {
        return $this->fileInfo["tmp_name"];
    }
    public function getDestination()
    {
        return $this->fileName. "." . $this->file_ext;
    }

    public function getError()
    {
        if (isset($this->error)) {
            return $this->error;
        }
        return null;
    }

    public function save($directory = '')
    {
        if ($this->isValid || $this->isValid()) {
            if (preg_match('/^.+(\.\.)/', $directory)) {
                return false;
            }

            $dir = ABS_PATH . '/public/files/' . ltrim(trim($directory), '/') . '/';
            return move_uploaded_file($this->fileInfo['tmp_name'], $dir . $this->getDestination());
        }
        return false;
    }

    public function getInfo($key)
    {
        if (isset($this->fileInfo[$key])) {
            return $this->fileInfo[$key];
        }
        return "";
    }

    public static function delete($source)
    {
        if (file_exists(ABS_PATH . $source)) {
            try {
                if (preg_match("/\/([\w\s]+\/)+/", $source, $matches)) {
                    $dest = str_replace($matches[0], "/tmp/", $source);
                    $batch = new Batch();
                    $batch->add(
                        new MoveFile(
                            Registry::getConfig()->get("FTP_ABS_PATH") . $source,
                            Registry::getConfig()->get("FTP_ABS_PATH") . $dest
                        )
                    );
                    $batch->execute();
                    return true;
                }
            } catch (Exception $e) {
                Debugger::log(
                    "Fehler beim Löschen des Bildes `$source`. Meldung: " . $e->getMessage(),
                    'Fehler'
                );
            }
        } else {
            Debugger::log("Fehler beim Löschen des Bildes `$source`. Meldung: Bild nicht gefunden.", 'Fehler');
        }
        return false;
    }

    public static function get($fileInfo)
    {
        return new File($fileInfo);
    }

    public static function getAllowedMimes()
    {
        return array(
            'image/jpeg',
            'image/png',
            'image/gif',
        );
    }
}
