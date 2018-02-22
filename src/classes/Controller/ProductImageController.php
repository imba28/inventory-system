<?php
namespace App\Controller;

use App\Models\ProductImage;

class ProductImageController extends ApplicationController
{
    protected $image;

    public function init()
    {
        parent::init();
        
        $this->beforeAction('delete', 'authenticateUser');
        $this->beforeAction(
            'delete',
            function ($params) {
                try {
                    $this->image = ProductImage::find($params['id']);
                } catch (\App\Exceptions\NothingFoundException $e) {
                    $this->error(404, "image not found");
                }
            }
        );
    }

    public function delete()
    {
        $this->image->remove();

        $this->respondTo(
            function ($wants) {
                $wants->json(
                    function () {
                        $this->view->assign("status", "ok");
                        $this->view->assign("message", "image deleted");
                    }
                );

                $wants->html(
                    function () {
                        $this->view->assign('image', $this->image);
                    }
                );
            }
        );
    }

    public function error($status, $message = null)
    {
        $this->response->setStatus($status);
        $this->response->flush();
    }
}
