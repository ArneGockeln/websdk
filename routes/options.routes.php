<?php
/**
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
});

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

    // Callable Test Functions
    $secureStringTests = array('!is_empty', 'isSecureString');
    $secureFloatTests = array('!is_empty', 'is_numeric');
    $secureEmailTests = array('!is_empty', 'isSecureEmail');

    $exceptionString = _("%s wird benötigt oder enthält ungültige Zeichen!");
    $exceptionNumeric = _("%s ist keine Zahl!");
    $exceptionEmail = _("%s ist keine gültige E-Mail Adresse!");

    // Required Fields are defined here with auto tests,
    // other fields or fields that have custom valid checks will be
    // defined in the foreach loop
    $tests = array(
        'core_events_shortbody_maxlength' => array(
            'name' => _('Maximale Anzahl Zeichen für die Kurzbeschreibung'),
            'tests' => $secureFloatTests,
            'exception' => $exceptionNumeric
        ),
        'core_uploads_userfiles_maxfilesize_in_mb' => array(
            'name' => _('Maximale Dateigröße für Benutzerdateien'),
            'tests' => $secureFloatTests,
            'exception' => $exceptionNumeric
        ),
        'core_session_time_limit' => array(
            'name' => _('Session Time Limit'),
            'tests' => $secureStringTests,
            'exception' => $exceptionString
        ),
        'core_mailer_from' => array(
            'name' => _('Absender Name'),
            'tests' => $secureStringTests,
            'exception' => $exceptionString
        ),
        'core_mailer_from_email' => array(
            'name' => _('Absender E-Mail'),
            'tests' => $secureEmailTests,
            'exception' => $exceptionEmail
        ),
        'core_mailer_admin_email' => array(
            'name' => _('Administrator E-Mail'),
            'tests' => $secureEmailTests,
            'exception' => $exceptionEmail
        ),
        'core_mailer_reply_email' => array(
            'name' => _('Antwort E-Mail'),
            'tests' => $secureEmailTests,
            'exception' => $exceptionEmail
        )
    );

    // Save Request?
    try {
        if($app->request->isPost()){
            $unsecureStringLocale = _('Der Text für %s enthält ungültige Zeichen!');

            $postVars = $app->request->post();
            $inputTab = getValue($postVars, 'inputTab');

            $data['activeTab'] = $inputTab;

            $postOptions = getValue($postVars, 'options');

            // save all options
            foreach($postOptions as $key => $value){
                // check options
                // allowed origins
                if($key == 'core_api_allowed_origins'){
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
                }

                // check smtp host
                if($key == 'core_mailer_smtp_host'){
                    if(!is_empty($value)){
                        if(!isSecureHost($value)) throw new Exception(sprintf($unsecureStringLocale, _('SMTP Host')));
                    }
                }
                // check smtp user
                if($key == 'core_mailer_smtp_user'){
                    if(!is_empty($value)){
                        if(!isSecureString($value)) throw new Exception(sprintf($unsecureStringLocale, _('SMTP Benutzer')));
                    }
                }

                // upload limits
                if($key == 'core_uploads_userfiles_maxfilesize_in_mb' || $key == 'core_uploads_logo_maxfilesize_in_mb'){
                    $value = trim(str_replace('MB', '', $value));
                }

                // normal tests
                if(array_key_exists($key, $tests)){
                    if(!isPassingAllTests($value, $tests[$key]['tests'])){
                        throw new Exception(sprintf(getValue($tests[$key], 'exception'), getValue($tests[$key], 'name')));
                    }
                }

                // save option
                $option = new Option($dbOptions[$key]['id']);
                //$option->setOptionCategory($category_key);
                $option->setOptionKey($key);
                $option->setOptionValue($value);
                if(!$option->save()){
                    throw new Exception(sprintf(_('Die Option "%s" konnte nicht gespeichert werden!'), $key));
                }

                // update frontend data
                $data[$key] = $value;
            }


            flashNowMessage(_('Einstellungen gespeichert!'), FlashStatus::SUCCESS);
        } else {
            $data['activeTab'] = 'default';
        }
    } catch(Exception $e){
        flashNowMessage($e->getMessage());
    }

    renderTemplate('core/options.twig', $data);
}