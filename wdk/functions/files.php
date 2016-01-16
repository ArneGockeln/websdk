<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

/**
 * Require app routes in folder routes/app/
 */
function loadAppRoutes(){
    $d = dir(WDK_APP_ROUTE_PATH);
    while(false !== ($entry = $d->read())){
        if($entry != '.' && $entry != '..'){
            if(isRouteFile($entry)){
                require_once(WDK_APP_ROUTE_PATH . $entry);
            }
        }
    }
}

/**
 * Require core routes in folder routes/core/
 */
function loadCoreRoutes(){
    $d = dir(WDK_CORE_ROUTE_PATH);
    while(false !== ($entry = $d->read())){
        if($entry != '.' && $entry != '..'){
            if(isRouteFile($entry)){
                require_once(WDK_CORE_ROUTE_PATH . $entry);
            }
        }
    }
}


/**
 * Get upload directory path
 *
 * @param string $addPath (optional) add a path to the uploaddir
 * @param bool|true $withTrailingSlash (optional) default with trailingslash
 * @return null|string
 * @throws Exception
 */
function getUploadDir($addPath = '', $withTrailingSlash = true){
    if(!defined('CFG_UPLOAD_DIR')){
        throw new Exception(_('CFG_UPLOAD_DIR ist nicht definiert!'));
    }

    $uploadDir = getOption('CFG_UPLOAD_DIR');
    if(!is_dir($uploadDir)){
        throw new Exception(sprintf("Das Verzeichnis %s ist nicht vorhanden!", $uploadDir));
    }

    if(!is_empty($addPath)){
        $uploadDir = getTrailingSlash($uploadDir);
    }

    $uploadDir .= $addPath;
    if(!is_dir($uploadDir)){
        throw new Exception(sprintf("Das Verzeichnis %s ist nicht vorhanden!", $uploadDir));
    }

    if($withTrailingSlash){
        $uploadDir = getTrailingSlash($uploadDir);
    }

    return $uploadDir;
}

/**
 * Get Mime Type of given file
 * @param string $file path to file
 * @return null|string
 */
function getMimeTypeOfFile($file){
    // Check mime types
    $mimetype = null;
    if(!is_file($file)){
        return null;
    }

    if(function_exists('finfo_open')){
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
    } else {
        // old school check php4/5
        $mimetype = strtolower(array_pop(explode('.', $file)));
    }

    return $mimetype;
}
?>