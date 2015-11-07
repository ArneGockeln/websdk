<?php
/**
 * iqz
 * Author: Arne Gockeln, WebSDK
 * Date: 16.10.15
 */

use Slim\Slim;
use WebSDK\Routes;
use WebSDK\Option;
use WebSDK\FlashStatus;
use WebSDK\UserTypeEnum;
use WebSDK\UserRightEnum;

global $app;

$app->group('/options', 'isUserOnline',  function() use($app){
    $app->map('/', 'route_handle_options')->via('GET', 'POST')->name(Routes::OPTIONS);
    $app->get('/categories/:ident/delete_confirm/:id', 'route_ajax_get_options_category_delete_confirm');
    $app->get('/categories/delete/:ident/:id', 'route_ajax_get_options_category_delete');
    $app->get('/categories/list/:ident', 'route_ajax_options_get_categories_list');
    $app->post('/categories/save_category/:ident', 'route_ajax_options_save_category');
});

/**
 * AJAX get categories list
 */
function route_ajax_options_get_categories_list($ident){
    $app = Slim::getInstance();

    try {
        if(!$app->request->isXhr() || !$app->request->isAjax()){
            throw new Exception(_('Diese Funktion erwartet einen Xhr/Ajax Request!'));
        }

        $data = array();
        $sql = '';
        switch($ident){
            case 'event':
                $sql = "SELECT * FROM " . \IQZ\DBTables::EVENT_CATEGORIES . " WHERE deleted = 0 ORDER BY id DESC";
                break;
            case 'iqz':
                $sql = "SELECT * FROM " . \IQZ\DBTables::IQZ_CATEGORIES . " WHERE deleted = 0 ORDER BY id DESC";
                break;
        }

        // set ident for delete modal
        $data['ident'] = $ident;
        // get list of event categories
        $data['categoryList'] = \WebSDK\Database::getInstance()->query($sql)->fetchList(true);

        $html = renderTemplate('app/ajax_category_list.twig', $data, true);

        ajaxResponse($html);
    } catch(Exception $e){
        ajaxResponse($e->getMessage(), 400);
    }
}

/**
 * Save AJAX Category
 */
function route_ajax_options_save_category($ident){
    $app = Slim::getInstance();

    try {
        // Security Check, redirect to index if false!
        if(!isUserType(UserTypeEnum::ADMINISTRATOR)) throw new Exception(_('Sie müssen Administrator sein!'));
        if(!isUserAuthorised(UserRightEnum::MANAGE_OPTIONS)) throw new Exception(_('Sie benötigen mehr Rechte um diese Aktion durchzuführen!'));
        if(!$app->request->isAjax() || !$app->request->isXhr()){
            throw new Exception(_('Diese Funktion erwartet einen Xhr/Ajax Request!'));
        }

        $postVars = $app->request->post();
        $id = (int)getValue($postVars, 'id');
        $name = getValue($postVars, 'name');

        // checks
        if(is_null($id)) throw new Exception(_('Die ID wird benötigt!'));
        if(is_empty($name)) throw new Exception(_('Der Name wird benötigt!'));
        if(!isSecureString($name)) throw new Exception(_('Der Name enthält ungültige Zeichen!'));
        if(strtolower($name) == 'undefined') throw new Exception(_('Der Name wird benötigt!'));

        $element = null;
        switch($ident){
            case 'event':
                $element = new \IQZ\Category($id);
                break;
            case 'iqz':
                $element = new \IQZ\IQZCategory($id);
                break;
        }

        if(!($element instanceof \IQZ\Category) && !($element instanceof \IQZ\IQZCategory)){
            throw new Exception(_('Ident code unbekannt!'));
        }

        $element->setName($name);
        if(!$element->save()){
            throw new Exception(_('Das hat nicht geklappt!'));
        }
    } catch(Exception $e){
        ajaxResponse($e->getMessage(), 400);
        exit();
    }

    ajaxResponse(_('Die Kategorie wurde gespeichert!'));
}

/**
 * Delete Event/IQZ Category with ajax
 * @param $id
 */
function route_ajax_get_options_category_delete($ident, $id){
    // Security Check, redirect to index if false!
    isUserType(UserTypeEnum::ADMINISTRATOR, 'index');
    isUserAuthorised(UserRightEnum::MANAGE_OPTIONS, 'index');

    try {
        if($id <= 0){
            throw new Exception(_('ID ist null oder kleiner als 0!'));
        }

        $element = null;
        switch($ident){
            case 'event':
                $element = new \IQZ\Category($id);
                break;
            case 'iqz':
                $element = new \IQZ\IQZCategory($id);
                break;
        }

        if(!($element instanceof \IQZ\Category) && !($element instanceof \IQZ\IQZCategory)){
            throw new Exception(_('Ident code unbekannt!'));
        }

        if(!$element->delete()){
            throw new Exception(_('Das hat nicht geklappt!'));
        }
    } catch(Exception $e){
        ajaxResponse($e->getMessage(), 400);
    }

    ajaxResponse(_('Kategorie gelöscht!'));
}

/**
 * GET delete confirmation box
 */
function route_ajax_get_options_category_delete_confirm($ident, $id){
    $message = '';
    try {
        // Security Check, redirect to index if false!
        if(!isUserType(UserTypeEnum::ADMINISTRATOR)) throw new Exception(_('Sie müssen Administrator sein!'));
        if(!isUserAuthorised(UserRightEnum::MANAGE_OPTIONS)) throw new Exception(_('Sie benötigen mehr Rechte um diese Aktion durchzuführen!'));

        if(is_empty($id)){
            throw new Exception(_('ID ist null oder kleiner als 0!'));
        }

        $element = null;
        $message = _('Soll die Kategorie "%s" wirklich gelöscht werden?');
        switch($ident){
            case 'event':
                $element = new \IQZ\Category($id);
                break;
            case 'iqz':
                $message = _('Soll die IQZ Kategorie "%s" wirklich gelöscht werden?');
                $element = new \IQZ\IQZCategory($id);
                break;
        }

        if(!($element instanceof \IQZ\Category) && !($element instanceof \IQZ\IQZCategory)){
            throw new Exception(_('Ident code unbekannt!'));
        }

        $message = sprintf($message, $element->getName());
    } catch(Exception $e){
        ajaxResponse($e->getMessage());
    }

    ajaxResponse($message);
}

/**
 * GET AND POST
 * Options
 */
function route_handle_options(){
    // Security Check, is user authorised to perform this action?
    isUserType(UserTypeEnum::ADMINISTRATOR, 'index');
    isUserAuthorised(UserRightEnum::MANAGE_OPTIONS, 'index');

    $app = Slim::getInstance();

    $data = array();
    // map option values for frontend
    $dbOptions = getOptions();
    foreach($dbOptions as $key => $option){
        $data[$key] = getValue($option, 'option_value');
    }

    // Save Request?
    try {
        if($app->request->isPost()){
            $unsecureStringLocale = _('Der Text für %s enthält ungültige Zeichen!');

            $postVars = $app->request->post();
            $inputTab = getValue($postVars, 'inputTab');

            switch($inputTab){
                case 'default': // default tab
                case 'email': // email tab
                case 'api': // api tab
                    $postOptions = getValue($postVars, 'options');

                    // save all options
                    foreach($postOptions as $category_key => $options){
                        if(!is_empty($options)){
                            foreach($options as $key => $value){
                                // check option
                                switch($key){
                                    case 'core_session_time_limit':
                                        if(!is_empty($value)){
                                            if(!isSecureString($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('Session Time Limit')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_from':
                                        if(!is_empty($value)){
                                            if(!isSecureString($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('Absender')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_from_email':
                                        if(!is_empty($value)){
                                            if(!isSecureEmail($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('Absender E-Mail')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_reply_email':
                                        if(!is_empty($value)){
                                            if(!isSecureEmail($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('Antwort E-Mail')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_admin_email':
                                        if(!is_empty($value)) {
                                            if(!isSecureEmail($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('Administrator E-Mail')));
                                            }
                                        } else {
                                            throw new Exception(_('Die Administrator E-Mail Adresse wird benötigt!'));
                                        }
                                        break;
                                    case 'core_mailer_smtp_host':
                                        if(!is_empty($value)) {
                                            if(!isSecureHost($value)){
                                                throw new Exception(sprintf($unsecureStringLocale, _('SMTP Host')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_smtp_user':
                                        if(!is_empty($value)) {
                                            if(!isSecureString($value)) {
                                                throw new Exception(sprintf($unsecureStringLocale, _('SMTP Benutzer')));
                                            }
                                        }
                                        break;
                                    case 'core_mailer_smtp_password':
                                        // do nothing
                                        break;
                                    case 'core_api_allowed_origins':
                                        if(!is_empty($value)){
                                            $hosts = explode(',', $value);
                                            if(is_array($hosts)) foreach($hosts as $hostKey => $hostValue){
                                                if(!isSecureHost($hostValue)){
                                                    throw new Exception(sprintf($unsecureStringLocale, (is_empty($hostValue) ? _('API Domain') : $hostValue)));
                                                }
                                            } else {
                                                if(!isSecureHost($value)){
                                                    throw new Exception(sprintf($unsecureStringLocale, _('API Domain')));
                                                }
                                            }
                                        }
                                        break;
                                }

                                // save option
                                $option = new Option($dbOptions[$key]['id']);
                                $option->setOptionCategory($category_key);
                                $option->setOptionKey($key);
                                $option->setOptionValue($value);
                                if(!$option->save()){
                                    throw new Exception(sprintf(_('Die Option "%s" konnte nicht gespeichert werden!'), $key));
                                }

                                // update frontend data
                                $data[$key] = $value;
                            }
                        }
                    }
                    break;
            }


            flashNowMessage(_('Einstellungen gespeichert!'), FlashStatus::SUCCESS);
        }
    } catch(Exception $e){
        flashNowMessage($e->getMessage());
    }

    renderTemplate('core/options.twig', $data);
}