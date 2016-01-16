<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 16.01.16
 *
 * All functions require the slim instace
 */

/**
 * Flash a message for next route request
 * @param string $message
 * @param string $type
 */
function flashMessage($message = '', $type = 'error'){
    $app = \Slim\Slim::getInstance();
    $app->flash($type, $message);
}

/**
 * Flash a message now
 * @param string $message
 * @param string $type
 */
function flashNowMessage($message, $type = 'error'){
    $app = \Slim\Slim::getInstance();
    $app->flashNow($type, $message);
}

/**
 * Use this in exception handling to flash a message and redirect to route name
 * @param $route string|array route name or array with route name and parameters
 * @param $message
 * @param string $status
 */
function flashRedirect($route, $message, $status = 'error'){
    $app = \Slim\Slim::getInstance();
    flashMessage($message, $status);

    if(is_array($route)){
        $app->redirectTo($route['route'], $route['params']);
    } else {
        $app->redirectTo($route);
    }
}

/**
 * Check if current resource uri equals $checkUri
 * ex. /projects == /projects
 * @param $checkUri
 * @return bool
 */
function isActiveUri($checkUri){
    $app = \Slim\Slim::getInstance();
    $requestUri = $app->request->getResourceUri();
    if(strlen($checkUri) > 1){
        if(strcasecmp($requestUri, $checkUri) == 0){
            return true;
        } else if(strpos($requestUri, $checkUri) !== false){
            return true;
        }
    } else {
        if(strcmp($checkUri, $requestUri) == 0){
            return true;
        }
    }
    return false;
}

/**
 * Render or Return Template html with optional values
 * @param $template
 * @param array $values
 * @param bool|false $return
 * @return string
 */
function renderTemplate($template, $values = array(), $return = false){
    global $twig;

    $app = \Slim\Slim::getInstance();

    // Get messages
    $messageTypes = array(
        'error',
        'info',
        'warning',
        'success'
    );
    foreach($messageTypes as $int => $messageType){
        $message = getValue($app->flashData(), $messageType);
        if(strlen($message)>0){
            $values['system_messages'][] = array('type' => $messageType, 'msg' => $message);
        }
    }

    $html = $twig->render($template, $values);
    if(!$return){
        echo $html;
    } else {
        return $html;
    }
}

/**
 * API Middleware to restrict access only to allowed origins
 * Origins can be configured in the options panel!
 * @return bool
 * @throws Exception
 *
 * @ToDo: return false by default and only for allowed origins return true!
 */
function isOriginAllowed(){
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

    $app = Slim::getInstance();

    // deny access from other hosts
    // can be configured in options menu
    $allowedOrigins = array();
    $allowedOrigins[] = getHttpHost();
    $optionAllowedOrigins = getOption('OPT_CORE_API_ALLOWED_ORIGINS');
    if(!is_empty($optionAllowedOrigins)){
        $optionAllowedOrigins = getArrayFromMixed($optionAllowedOrigins);
        foreach($optionAllowedOrigins as $int => $allowedHost){
            $allowedOrigins[] = getTrailingSlash(trim($allowedHost));
        }
    }

    $origin = getHttpOrigin();
    if(!is_empty($origin)){
        if(!in_array(getTrailingSlash($origin), $allowedOrigins)){
            ajaxResponse(sprintf(_('HTTP_ORIGIN %s ist nicht erlaubt!'), $origin), 400);
            return false;
        }
    }

    // if this is an options request, return true
    if($app->request->isOptions()){
        return true;
    }

    return true;
}
?>