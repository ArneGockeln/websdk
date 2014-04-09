<?php
/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */
class UserSession {    
  
  public function __construct(){
    
  }
  
  /**
   * Register a new session
   * @param type $userId
   * @return boolean
   */
  public static function register($userId = false){
    
    if($userId <= 0){
      return;
    }
        
    $user = new User($userId);
    
    // REGISTER NEW SESSION    
    if($user->id>0){
      
      UserSession::addObject('userId', $user->id);
      UserSession::addObject('userName', $user->firstname . ' ' . $user->lastname);
      UserSession::addObject('userLocale', (isset($user->locale) && !isEmptyString($user->locale) ? $user->locale : 'de_DE'));
      
      // insert session data to database
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USER_SESSIONS);
      $mysql->setColumns(array(
          'session_id' => session_id(),
          'user_id' => (int) $user->id,
          'user_locale' => (isset($user->locale) && !isEmptyString($user->locale) ? $user->locale : 'de_DE')
      ));
      $mysql->setColumn('lastactivity', "UNIX_TIMESTAMP()+(60*" . CFG_SESSION_LIMIT . ")", false);
      
      $mysql->insertRow();
      
      if($mysql->hasAffectedRows()){
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * Check if user is online
   * @return boolean
   */
  public static function isOnline(){    
    $mysql = new mysqlDatabase();
    $mysql->setTable(CFG_DBT_USER_SESSIONS);
    $mysql->setColumns('*');
    $mysql->setRestriction('session_id', '=', session_id());
    
    $row = $mysql->fetchRow();
        
    if($mysql->hasRows()){
      $now = time();
      if($now < $row->lastactivity){
        
        $user = new User($row->user_id);
        if($user->locked == 0){
          self::register($row->user_id);
        
          return true;
        } else {
          self::destroy($row->user_id);
          registerMessage(_('Benutzer ist gesperrt!'), true);
        }
      } else {
        self::destroy($row->user_id);
      }
    }
    
    return false;
  }
  
  /**
   * Destroy all database sessions of $userid
   * @param type $userid
   * @return boolean if succeeded
   */
  public static function destroy($userid = 0){
    if($userid == 0){
      // try to get current userid
      $userid = self::getCurrentUserId();
    }
    
    if($userid>0){
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USER_SESSIONS);
      $mysql->setColumn('user_id');
      $mysql->setRestriction('user_id', '=', $userid);
      
      $mysql->deleteRows();
      
      if($mysql->hasAffectedRows()){
        unset($_SESSION[CFG_SESSION_INDEX]);
        @session_destroy();
        
        return true;
      }
    }
    
    return false;
  }
  
    /**
   * Get current userid
   * @return boolean
   */
  public static function getCurrentUserId(){
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      return $_SESSION[CFG_SESSION_INDEX]['userId'];
    }
    return false;
  }
  
  /**
   * Get current username
   * @return boolean|string
   */
  public static function getCurrentUsername(){
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      return $_SESSION[CFG_SESSION_INDEX]['userName'];
    }
    return false;
  }
  
  /**
   * Get current locale
   * @return string
   */
  public static function getCurrentLocale($fromDb = false){
    
    if($fromDb){
      $mysql = new mysqlDatabase(CFG_DBT_USER_SESSIONS);
      $mysql->setColumns('*');
      $mysql->setRestriction('session_id', '=', session_id());
      $mysql->setRestriction('user_id', '=', getCurrentUID());
      
      $data = $mysql->fetchRow();
      if($mysql->hasRows()){
        return $data->user_locale;
      }
    }
    
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      return $_SESSION[CFG_SESSION_INDEX]['userLocale'];
    }
  }
  
  /**
   * Add or updates a session object in assetplan session object
   * @param type $identifer
   * @param type $obj
   */
  public static function addObject($identifer, $obj){
    // if object exists, delete it
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      if(array_key_exists($identifer, $_SESSION[CFG_SESSION_INDEX])){
        unset($_SESSION[CFG_SESSION_INDEX][$identifer]);
      }
    }
    // set new value
    $_SESSION[CFG_SESSION_INDEX][$identifer] = $obj;
  }
  
  /**
   * Get a session object from assetplan session
   * @param type $identifer
   * @return mixed
   */
  public static function getObject($identifer){
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      if(array_key_exists($identifer, $_SESSION[CFG_SESSION_INDEX])){
        return $_SESSION[CFG_SESSION_INDEX][$identifer];
      }
    }
    return false;
  }
  
  /**
   * Removes a session object from assetplan session object
   * @param type $identifer
   */
  public static function removeObject($identifer){
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      unset($_SESSION[CFG_SESSION_INDEX][$identifer]);
    }
  }
  
  /**
   * Debugs current session object
   */
  public static function debugSession(){
    if(array_key_exists(CFG_SESSION_INDEX, $_SESSION)){
      echo '<pre>' . print_r($_SESSION[CFG_SESSION_INDEX], true) . '</pre>';
    } else {
      echo '<pre>' . print_r($_SESSION, true) . '</pre>';
    }
  }
  
  /**
   * Change current user locale
   * @global type $localeList
   * @param type $newLocale
   * @return boolean
   */
  public static function changeLocale($newLocale){
    if(is_online()){
      global $localeList;
      if(in_array($newLocale, $localeList)){
        $mysql = new mysqlDatabase(CFG_DBT_USER_SESSIONS);
        $mysql->setColumns(array(
            'session_id' => session_id(),
            'user_id' => getCurrentUID(),
            'user_locale' => $newLocale
        ));
        
        $mysql->setRestrictions(array(
            array('column' => 'user_id', 'operator' => '=', 'value' => getCurrentUID()),
            array('column' => 'session_id', 'operator' => '=', 'value' => session_id())
        ));
        
        $mysql->updateRow();
        
        if($mysql->hasAffectedRows()){
          
          UserSession::addObject('userLocale', $newLocale);
          
          return true;
        }
      }
    }
    return false;
  }
}
?>
