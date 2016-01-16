<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 16.01.16
 *
 * Functions that are performing tasks on html form data
 */

use WebSDK\UserSession;

/**
 * Save $array to session
 * @param array $array
 */
function saveFormData($array){
    if(!is_empty($array)){
        $string = trim(json_encode($array));
        $base64 = base64_encode($string);
        UserSession::addValue('formData', $base64);
    }
}

/**
 * Restore form data
 * @return mixed|null
 */
function restoreFormData(){
    $formDataString = base64_decode(UserSession::getValue('formData'));
    if(!is_empty($formDataString)){
        return json_decode($formDataString, true);
    }
    return null;
}

/**
 * Clear form data
 */
function clearFormData(){
    UserSession::addValue('formData', '');
    UserSession::remValue('formData');
}

/**
 * Check if FILTER formData is empty
 * @param $formData
 * @return bool
 * @throws Exception
 */
function is_filter_empty($formData){
    if(!is_empty($formData)){
        if(getValue($formData, 'inputIsFilter') == 1){
            return false;
        }
    }
    return true;
}

/**
 * Check if $formData contains one or more elements from $elements
 * @param $formData
 * @param array $elements
 * @return bool
 * @throws Exception
 */
function hasElements($formData, $elements = array()){
    $matches = 0;
    if(is_array($formData)){
        if(!is_empty($formData)){
            foreach($formData as $key => $value){
                if(in_array($key, $elements)){
                    $matches++;
                }
            }
        }
    }
    return $matches > 0;
}

/**
 * Parse formData with input fields "input" prefix
 *
 * @param array $formData Reference to formData array
 */
function parseFormData(&$formData){
    if(is_array($formData) && count($formData) > 0) {
        $newFormData = array();
        foreach($formData as $key => $value){
            if(strpos(substr($key, 0, 5), 'input') !== false) {
                if(strtolower($key) == 'inputid'){
                    $varname = 'id';
                } else {
                    $varname = parseVarname($key, 5);
                }

                $newFormData[strtolower($varname)] = $value;
                unset($formData[$key]);
            }
        }
        $formData = array_merge($formData, $newFormData);
    }
}

/**
 * Return parsed varname and transform strings like CompanyContact to Company_Contact
 *
 * @see classToArray() Function to parse php class to array
 * @see parseFormData() Function to parse html form data
 *
 * @param string $varname the string to be parsed
 * @param int $substrLength int value for substr -> how many chars to cut before first capital char?
 *
 * @return string
 */
function parseVarname($varname, $substrLength = 0){
    // old regex /[A-Z]/
    preg_match_all('/([A-Z]+[a-z])/', substr($varname, $substrLength), $matches);
    $varname = substr($varname, $substrLength);
    $matchCount = count($matches[0]);

    if ($matchCount > 1) {
        for ($i = 1; $i <= $matchCount; $i++) {
            $varname = str_replace($matches[0][$i], '_' . $matches[0][$i], $varname);
            // with double names like Company_Contact, the first element gets an underscore.
            // it results in _Company_Contact because the matches[0][$i] = C will be replaced twice
            // workaround: cut the first underscore
            if (substr($varname, 0, 1) == '_') {
                $varname = substr($varname, 1, strlen($varname) - 1);
            }
        }
    }

    return $varname;
}
?>