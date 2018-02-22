<?php
namespace App\Controller;

use \DateTime;
use App\Models\Log;

class LogController extends ApplicationController
{
    public function index()
    {
        $this->view->setTemplate('log');

        $this->view->assign(
            'logs',
            Log::findByFilter(
                array('createDate', '>', date('Y-m-d', time() - 86400 * 7))
            )
        );
    }

    public function init()
    {
        parent::init();
        $this->beforeAction('index', 'authenticateUser');
    }

    public function error($status)
    {
    }
}
