<?php
/**
 * WebSDK API
 * Author: Arne Gockeln, WebSDK
 * Date: 05.11.15
 */

use \Slim\Slim;
use \WebSDK\Database;

$app = Slim::getInstance();
$app->get('/api', 'isOriginAllowed', function() use($app){
    // handle api calls here
});
?>