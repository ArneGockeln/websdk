<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 16.01.16
 *
 * Functions to handle locales
 */

/**
 * Set locale and bind textdoamin
 * @param string $locale
 * @param string $textDomain default is WebSDK
 */
function rebindLocale($locale, $textDomain = 'WebSDK'){
    putenv('LC_ALL='.$locale);
    setlocale(LC_ALL, $locale);
    setlocale(LC_TIME, $locale);
    bindtextdomain($textDomain, WDK_LOCALE_PATH);
    bind_textdomain_codeset($textDomain, 'UTF-8');
    textdomain($textDomain);
}

/**
 * Looks into path WDK_LOCALE_PATH
 * and lists all locale directories
 * @return array
 */
function getLocales(){
    $dir = dir(WDK_LOCALE_PATH);
    $list = array();
    while(false !== ($entry = $dir->read())){
        if(!in_array($entry, ['.', '..'])){
            if(is_dir(getTrailingSlash(WDK_LOCALE_PATH) . $entry)){
                $list[] = $entry;
            }
        }
    }
    return $list;
}
?>