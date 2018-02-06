<?php
namespace App\Controller;

class FileController extends ApplicationController
{
    private static $allowedFileTypes = array('png', 'jpg', 'jpeg', 'png', 'gif', 'html', 'css', 'js', 'woff', 'ttf');

    public function main($params)
    {
        $requested_file = ABS_PATH . $this->request->getHeader('REQUEST_URI');

        if (file_exists($requested_file)) {
            $path_split = explode('/', $requested_file);
            $file_split = explode('.', end($path_split));

            if (in_array(end($file_split), self::$allowedFileTypes)) {
                $this->response->setStatus(200);

                $this->response->addHeader('Content-Type', fileext_to_mime($requested_file));
                $this->response->addHeader('Content-Length', filesize($requested_file));
                $this->response->append(file_get_contents($requested_file));

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
