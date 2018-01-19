<?php
namespace App\Controller;

class InventurController extends \App\BasicController implements \App\Interfaces\Controller {
    protected $inventur;

    public function __construct($layout = 'default') {
        parent::__construct($layout);

        $this->inventur = new \App\Inventur();
    }

    public function main($params) {
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());
        $this->view->assign('inventur', $this->inventur);

        if(isset($params['action'])) {
            if($this->inventur->isStarted()) {
                if($params['action'] == 'registered') {
                    $this->view->assign('products', $this->inventur->getRegisteredItems());
                    $this->view->setTemplate('inventur-registered');
                }
                elseif($params['action'] == 'missing') {
                    $this->view->assign('products', $this->inventur->getMissingItems());
                    $this->view->setTemplate('inventur-missing');
                }
                else {
                    $this->error(404, 'Seite nicht gefunden!');
                }
            }
            else {
                $this->response->redirect('/inventur');
            }
        }
        else {
            $this->view->setTemplate('inventur');
        }
    }

    public function actionInventur() {
        if($this->inventur->isStarted()) {
            if($this->request->issetParam('action')) {
                if($this->request->getParam('action') == 'start') {
                    $this->startInventur();
                }
                elseif($this->request->getParam('action') == 'end') {
                    $this->endInventur();
                }
                elseif($this->request->getParam('action') == 'scan_product') {
                    $this->scanProduct($this->request->getParam('invNr'));
                }
                else throw new \App\Exceptions\InvalidOperationException('not a valid action!');
            }
        }
        else {
            $this->response->redirect('/inventur');
        }
    }

    private function scanProduct($invNr) {
        try {
            $product = \App\Models\Product::grab($invNr, 'invNr');

            try {
                if($this->inventur->registerProduct($product)) {
                    \App\System::getInstance()->addMessage('success', "Inventarnummer <em>{$invNr}</em> wurde erfasst!");
                }
                else {
                    \App\System::getInstance()->addMessage('error', "Inventarnummer <em>{$invNr}</em> konnte nicht erfasst werden.");
                }
            }
            catch(\App\QueryBuilder\NothingChangedException $e) {
                \App\System::getInstance()->addMessage('info', "Inventarnummer <em>{$invNr}</em> wurde bereits erfasst.");
            }
        }
        catch(\App\Exceptions\NothingFoundException $e) {
            \App\System::getInstance()->addMessage('error', "Inventarnummer <em>{$invNr}</em> wurde nicht gefunden. Möchtest du sie <a href='/products/add' target='_blank'>anlegen</a>?");
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    private function startInventur() {
        if($this->inventur->isStarted()) {
            \App\System::getInstance()->addMessage('error', 'Inventur wurde bereits gestartet!');
        }
        else {
            $this->inventur->start();
            \App\System::getInstance()->addMessage('success', 'Inventur wurde erfolgreich gestartet!');
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    private function endInventur() {
        try {
            $this->inventur->end();
            \App\System::getInstance()->addMessage('success', 'Inventur wurde erfolgreich beendet!');
        }
        catch(\App\Exceptions\InventurNotFinishedException $e) {
            \App\System::getInstance()->addMessage('error', $e->getMessage());
        }
        catch(\App\Exceptions\InvalidOperationException $e) {
            \App\System::getInstance()->addMessage('error', $e->getMessage());
        }

        $this->view->assign('inventur', $this->inventur);
        $this->view->assign('lastInventur', \App\Inventur::getLastInventur());

        $this->view->setTemplate('inventur');
    }

    public function error($status, $message = null) {
        $this->response->setStatus($status);
        $this->view->assign('errorCode', $status);
        if(!is_null($message)) $this->view->assign('errorMessage', $message);

        $this->view->setTemplate('error');
    }
}
?>