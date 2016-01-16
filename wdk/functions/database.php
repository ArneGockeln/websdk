<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 16.01.16
 *
 * Functions that are interacting with or preparing data for the database
 */

/**
 * Restore database values to object reference
 * @param array $dbVars
 * @param object $class
 */
function restoreFromDB($dbVars = array(), &$class){
    if(!is_array($dbVars)){
        $dbVars = (array)$dbVars;
    }
    $classVars = classToArray($class);
    foreach($classVars as $key => $value){
        $callable = 'set' . strtoupper(substr($key, 0, 1)) . substr($key, 1);

        // overwrite callable if underscores are found
        if(strpos($key, '_') !== false){
            $parts = explode('_', $key);
            $callable = 'set';
            foreach($parts as $int => $part){
                $callable .= strtoupper(substr($part, 0, 1)) . substr($part, 1);
            }
        }

        if(array_key_exists($key, $dbVars)){
            $param = $dbVars[$key];
            call_user_func_array(array($class, $callable), array($param));
        }
    }
}

/**
 * Prepare class for database insert or update
 * @param $class
 * @return array
 * @throws Exception
 */
function prepareForDB($class){
    if(!is_object($class)){
        throw new Exception("Object is not a class!");
    }

    $vars = classToArray($class);

    // strip ID if its value is 0
    if(array_key_exists('id', $vars)){
        if(getValue($vars, 'id') == 0){
            unset($vars['id']);
        }
    }

    // unescape lastmod value
    if(array_key_exists('lastmod', $vars)){
        $vars['lastmod'] = array('value' => 'NOW()', 'escape' => false);
    }

    return $vars;
}

/**
 * Converts class to array
 * @param $object
 * @return array
 */
function classToArray($object){
    $array = array();
    if(is_object($object)){
        $className = get_class($object);

        $methods = get_class_methods($className);

        // Version 1 without _ varnames
        /*foreach($methods as $int => $method){
            if(strcmp(substr($method, 0, 3), 'get') == 0){
                $array[strtolower(substr($method, 3))] = call_user_func(array($object, $method));
            }
        }*/
        // Version 2 with _ var_names
        foreach($methods as $int => $method){
            // rewrite getters with underscores
            if(strcmp(substr($method, 0, 3), 'get') == 0){
                preg_match_all('/[A-Z]/', substr($method, 3), $matches);
                $varname = substr($method, 3);
                $matchCount = count($matches[0]);
                if($matchCount > 1) {
                    for($i = 1; $i <= $matchCount; $i++){
                        $varname = str_replace($matches[0][$i], '_' . $matches[0][$i], $varname);
                    }
                }

                $array[strtolower($varname)] = call_user_func(array($object, $method));
            }
        }
    }
    return $array;
}

/**
 * Checks if $username is not in use!
 * @param string $username
 * @return bool
 */
function is_username_available($username){
    $mysql = WebSDK\Database::getInstance();
    $count = $mysql->query("SELECT COUNT(*) as c FROM " . \WebSDK\DBTables::USERS . " WHERE username = '" . $username . "' OR username = '" . strtolower($username) . "' OR username = '" . strtoupper($username) . "'")->fetchRow();
    if($count->c == 0){
        return true;
    }
    return false;
}

/**
 * Checks if $email is not in use!
 * @param string $email
 * @return bool
 */
function is_email_available($email){
    $mysql = WebSDK\Database::getInstance();
    $count = $mysql->query("SELECT COUNT(*) as c FROM " . \WebSDK\DBTables::USERS . " WHERE email = '" . $email . "' OR email = '" . strtolower($email) . "' OR email = '" . strtoupper($email) . "'")->fetchRow();
    if($count->c == 0){
        return true;
    }
    return false;
}

/**
 * Get all options
 * @param bool|true $asKeyList returns array[option_key] = option list
 * @return array
 */
function getOptions($asKeyList = true){
    $mysql = \WebSDK\Database::getInstance();
    $sql = "SELECT * FROM " . \WebSDK\DBTables::OPTIONS;
    $mysql->query($sql);
    $list = $mysql->fetchList(true);

    if($asKeyList) {
        $options = array();
        if(!is_empty($list)) foreach($list as $int => $option){
            $options[$option['option_key']] = $option;
        }

        return $options;
    }
    return $list;
}
?>