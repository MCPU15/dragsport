<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

use app\models as Model;

$app->get('/', function() use($app) {
    return $app->json(array()); 
});


/**
    * Obtiene los administradores
    *
    * @return json
*/  
$app->get('/admins', function() use($app) {
    $a = new Model\Admins; 

    return $app->json([
    	"data" => $a->get(true)
    ]);   
});