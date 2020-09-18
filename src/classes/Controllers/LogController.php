<?php
namespace App\Controllers;

use \DateTime;
use App\Models\Log;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
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

    public function error($status)
    {
    }
}
