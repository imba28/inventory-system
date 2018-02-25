<?php
namespace App\Controllers;

class FileController extends ApplicationController
{
    private static $allowedFileTypes = array('png', 'jpg', 'jpeg', 'png', 'gif', 'html', 'css', 'js', 'woff', 'ttf');

    public function main($params)
    {
        $requestedFile = ABS_PATH . $this->request->getHeader('REQUEST_URI');

        if (file_exists($requestedFile)) {
            $pathSplit = explode('/', $requestedFile);
            $fileSplit = explode('.', end($pathSplit));

            if (in_array(end($fileSplit), self::$allowedFileTypes)) {
                $this->response->setStatus(200);

                $this->response->addHeader('Content-Type', fileext_to_mime($requestedFile));
                $this->response->addHeader('Content-Length', filesize($requestedFile));
                $this->response->append(file_get_contents($requestedFile));

                $this->response->flush();
            } else {
                $this->error(403);
            }
        }
    }

    public function error($status)
    {
        $this->response->setStatus(403);
        $this->response->append("<h1>{$status}</h1>");
        $this->response->flush();
    }
}
