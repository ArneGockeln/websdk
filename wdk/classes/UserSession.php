<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

namespace WebSDK;

class UserSession
{
    const SESSION_IDENT = 'c2f66692b862fc2a586a910d7e5854b1';

    private static $currentUser = null;
    private static $instance = null;

    public function __construct(){

    }

    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Get Session Limit in the following order OPT_CORE_SESSION_TIME_LIMIT => CFG_SESSION_LIMIT => DEFAULT 15 Minutes
     * @return int
     * @throws \Exception
     */
    public static function getSessionLimit(){
        $session_time_limit = getOption('opt_core_session_time_limit'); // from options page
        if(is_empty($session_time_limit)){
            $session_time_limit = getOption('cfg_session_limit'); // from config file
            if(is_empty($session_time_limit)){
                $session_time_limit = 15; // default value 15 minutes
            }
        }
        return $session_time_limit;
    }

    /**
     * Register user session
     * @param User $user
     */
    public static function register($user){
        if($user->getId() > 0){
            self::getInstance();
            self::addValue('uid', $user->getId());
            self::addValue('user_firstname', $user->getFirstname());
            self::addValue('user_lastname', $user->getLastname());
            self::addValue('user_locale', $user->getLocale());

            $values = array(
                'session_id' => session_id(),
                'uid' => (int) $user->getId(),
                'lastactivity' => array('value' => "UNIX_TIMESTAMP()+(60*" . self::getSessionLimit() . ")", 'escape' => false)
            );

            $mysql = Database::getInstance();
            $sql = $mysql->getParsedSql("INSERT INTO " . DBTables::SESSIONS . " (%s) VALUES(%s)", $values);
            $sql .= $mysql->getParsedSql("ON DUPLICATE KEY UPDATE %s", $values);
            $mysql->query($sql);
        }
    }

    /**
     * Destroy current user session
     * @param $uid
     * @return bool
     * @throws \Exception
     */
    public static function destroy($uid){
        $mysql = Database::getInstance();
        $mysql->query("DELETE FROM " . DBTables::SESSIONS . " WHERE uid = '". $uid ."'");
        if($mysql->hasAffectedRows()){
            unset($_SESSION[self::SESSION_IDENT]);
            @session_destroy();

            return true;
        }

        return false;
    }

    /**
     * Check if current session is active
     * @return bool
     * @throws \Exception
     */
    public static function isOnline(){
        self::getInstance();
        $mysql = Database::getInstance();
        $dbSession = $mysql->query("SELECT * FROM " . DBTables::SESSIONS . " WHERE session_id = '" . session_id() . "'")->fetchRow();

        if(!$mysql->hasRows()){
            return false;
        }

        if($dbSession->uid > 0){
            $now = time();
            if($now < $dbSession->lastactivity){

                $user = new User($dbSession->uid);
                if($user->getLocked() == 0 && $user->getDeleted() == 0){
                    self::register($user);

                    return true;
                } else {
                    // user is locked
                    self::destroy($dbSession->uid);
                }
            } else {
                self::destroy($dbSession->uid);
            }
        }

        return false;
    }

    /**
     * Add key/value pair to user session
     * @param string $key
     * @param mixed $value
     */
    public static function addValue($key, $value){
        $_SESSION[self::SESSION_IDENT][$key] = $value;
    }

    /**
     * Get value of key in user session
     * @param $key
     * @return mixed
     */
    public static function getValue($key){
        return getValue(getValue($_SESSION, self::SESSION_IDENT), $key);
    }

    /**
     * Get current active locale, or default locale
     * @return mixed|string like de_DE
     * @throws \Exception
     */
    public static function getLocale(){
        $locale = self::getValue('user_locale');
        if(is_empty($locale)){
            $locale = 'de_DE';
        }
        return $locale;
    }

    /**
     * Remove value of key in user session
     * @param $key
     */
    public static function remValue($key){
        unset($_SESSION[self::SESSION_IDENT][$key]);
    }

    /**
     * Get current user object
     * @return User
     */
    public static function getUser(){
        $instance = self::getInstance();
        if(is_null($instance::$currentUser)){
            $instance::$currentUser = new User(self::getValue('uid'));
        }

        return $instance::$currentUser;
    }

    /**
     * Dump current session data
     */
    public static function debug(){
        self::getInstance();
        debug(getValue($_SESSION, self::SESSION_IDENT));
    }
}