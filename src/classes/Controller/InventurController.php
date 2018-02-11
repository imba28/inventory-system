<?php
namespace App\Controller;

class InventurController extends ApplicationController
{
    protected $inventur;

    public function __construct($responseType = 'html', $layout = 'default')
    {
        parent::__construct($responseType, $layout);

        $this->authenticateUser();
        $this->inventur = new \App\Inventur();
    }

    public function main($params)
    {
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());
        $this->view->assign('inventur', $this->inventur);

        if (isset($params['action'])) {
            if ($this->inventur->isStarted()) {
                if ($params['action'] == 'registered') {
                    $this->view->assign('products', $this->inventur->getRegisteredItems());
                    $this->view->assign('inventurActions', $this->inventur->getInventurActions());
                    $this->view->setTemplate('inventur-registered');
                } elseif ($params['action'] == 'missing') {
                    $this->view->assign('products', $this->inventur->getMissingItems());
                    $this->view->setTemplate('inventur-missing');
                } else {
                    $this->error(404, 'Seite nicht gefunden!');
                }
            } else {
                $this->response->redirect('/inventur');
            }
        } else {
            $this->view->setTemplate('inventur');
        }
    }

    public function list()
    {
        $inventurList = \App\Models\Inventur::all();

        $this->view->assign('inventurList', $inventurList);
        $this->view->setTemplate('inventur-list');
    }

    public function actionInventur()
    {
        if ($this->request->issetParam('action')) {
            if ($this->request->getParam('action') == 'start') {
                if (!$this->inventur->isStarted()) {
                    $this->startInventur();
                } else {
                    $this->response->redirect('/inventur');
                }
            } elseif ($this->request->getParam('action') == 'end') {
                if ($this->inventur->isStarted()) {
                    $this->endInventur();
                } else {
                    $this->response->redirect('/inventur');
                }
            } elseif ($this->request->getParam('action') == 'scan_product') {
                $this->scanProduct($this->request->getParam('invNr'));
            } elseif ($this->request->getParam('action') == 'missing_product') {
                $this->missingProduct($this->request->getParam('invNr'));
            } else {
                throw new \App\Exceptions\InvalidOperationException('not a valid action!');
            }
        }
    }

    public function show($params)
    {
        if (isset($params['id'])) {
            try {
                $inventur = \App\Models\Inventur::find($params['id']);
                $missingProducts = array();
                $scannedProducts = array();

                try {
                    $missingProducts = \App\Models\InventurProduct::findByFilter(array(
                        array('inventur', '=', $inventur),
                        'AND',
                        array('missing', '=', 1)
                    ));
                } catch (\App\Exceptions\NothingFoundException $e) {
                }

                try {
                    $scannedProducts = \App\Models\InventurProduct::findByFilter(array(
                        array('inventur', '=', $inventur),
                        'AND',
                        array('in_stock', '=', 1)
                    ));
                } catch (\App\Exceptions\NothingFoundException $e) {
                }

                $this->view->assign('inventur', $inventur);
                $this->view->assign('missingProducts', $missingProducts);
                $this->view->assign('scannedProducts', $scannedProducts);
                $this->view->setTemplate('inventur-detail');

                return;
            } catch (\App\Exceptions\NothingFoundException $e) {
            }
        }

        $this->error(404, 'Inventur wurde nicht gefunden!');
    }

    private function scanProduct($invNr)
    {
        try {
            $product = \App\Models\Product::find($invNr, 'invNr');

            try {
                if ($this->inventur->registerProduct($product)) {
                    \App\System::success("Inventarnummer <em>{$invNr}</em> wurde erfasst!");
                } else {
                    \App\System::error("Inventarnummer <em>{$invNr}</em> konnte nicht erfasst werden.");
                }
            } catch (\App\QueryBuilder\NothingChangedException $e) {
                \App\System::info("Inventarnummer <em>{$invNr}</em> wurde bereits erfasst.");
            }
        } catch (\App\Exceptions\NothingFoundException $e) {
            \App\System::error(
                "Inventarnummer <em>{$invNr}</em> wurde nicht gefunden.
                Möchtest du sie <a href='/products/add' target='_blank'>anlegen</a>?"
            );
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    private function missingProduct($invNr)
    {
        try {
            $product = \App\Models\Product::find($invNr, 'invNr');

            try {
                if ($this->inventur->missingProduct($product)) {
                    \App\System::success("Inventarnummer <em>{$invNr}</em> wurde als fehlend markiert!");
                } else {
                    \App\System::error("Inventarnummer <em>{$invNr}</em> konnte nicht erfasst werden.");
                }
            } catch (\App\QueryBuilder\NothingChangedException $e) {
                \App\System::info("Inventarnummer <em>{$invNr}</em> wurde bereits erfasst.");
            }
        } catch (\App\Exceptions\NothingFoundException $e) {
            \App\System::error(
                "Inventarnummer <em>{$invNr}</em> wurde nicht gefunden.
                Möchtest du sie <a href='/products/add' target='_blank'>anlegen</a>?"
            );
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    private function startInventur()
    {
        if ($this->inventur->isStarted()) {
            \App\System::error('Inventur wurde bereits gestartet!');
        } else {
            $this->inventur->start($this->getCurrentUser());
            \App\System::success('Inventur wurde erfolgreich gestartet!');
            $this->redirectToRoute('/inventur', 'GET');
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    private function endInventur()
    {
        try {
            $this->inventur->end();
            \App\System::success('Inventur wurde erfolgreich beendet!');
        } catch (\App\Exceptions\InventurNotFinishedException $e) {
            \App\System::error($e->getMessage());
        } catch (\App\Exceptions\InvalidOperationException $e) {
            \App\System::error($e->getMessage());
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    public function error($status, $message = null)
    {
        $this->response->setStatus($status);
        $this->view->assign('errorCode', $status);
        if (!is_null($message)) {
            $this->view->assign('errorMessage', $message);
        }

        $this->view->setTemplate('error');
    }
}
