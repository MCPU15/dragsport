<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace app\controllers;

use app\models as Model;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Controllers\Controllers;
use Ocrend\Kernel\Controllers\IControllers;
use Ocrend\Kernel\Router\IRouter;

/**
 * Controlador admins/
*/
class adminsController extends Controllers implements IControllers {

    public function __construct(IRouter $router) {
        parent::__construct($router);
        global $config;
        $a = new Model\Admins($router);

        switch($this->method) {
        	case 'create':
                $this->template->display('admins/create');
            break;
            case 'edit':
                if ($this->isset_id && false != ($data = $a->get(false))) {
                    $this->template->display('admins/edit', array(
                        'data' => $data[0],
                        'images_dir' => Model\Admins::IMAGE_DIR 
                    ));
                }else{
                    Helper\Functions::redir($config['build']['url']. 'admins');
                }
                
            break;
            case 'delete':
                $a->delete();
            break;
            default:
                $this->template->display('admins/admins', array(
                    'data' => $a->get()
                ));
            break;
        }
    }
}