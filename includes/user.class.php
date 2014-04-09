<?php
/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

class User {
  public $id;
  public $firstname;
  public $lastname;
  public $email;
  public $pwd;
  public $locale;
  public $locked;
  public $roles;
  public $lastmod;
  
  /**
   * If $id > 0 then data is loaded from database
   * @param type $id
   */
  public function __construct($id = 0) {
    $this->load($id);
  }
  
  public function load($id){
    if($id>0){
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USERS);
      $mysql->setColumns('*');
      $mysql->setRestriction('id', '=', $id);
      
      $user = $mysql->fetchRow();
      
      if(count($user)>0){
        $this->id = $id;
        $this->firstname = $user->firstname;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->locale = $user->locale;
        $this->pwd = $user->pwd;
        $this->locked = $user->locked;
        $this->roles = $user->roles;
        $this->lastmod = $user->lastmod;
      }
    }
  }
  
  /**
   * Save user to database if email is correct
   * @return boolean
   */
  public function save(){
    if(is_email($this->email)){
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USERS);
      
      if($this->id>0){
        $mysql->setColumn('id', $this->id);
      }
      
      $mysql->setColumns(array(
          'firstname' => $this->firstname,
          'lastname' => $this->lastname,
          'email' => $this->email,
          'locale' => $this->locale,
          'locked' => $this->locked,
          'roles' => $this->roles
      ));
      
      $mysql->setColumn('lastmod', 'NOW()', false);
      
      $mysql->setRestriction('id', '=', $this->id);
            
      $mysql->updateRow();
      if($mysql->hasAffectedRows()){
        if($this->id <= 0){
          $this->id = $mysql->last_insert_id;
        }
        return true;
      }
    }
    return false;
  }
  
  /**
   * Change user password to newpassword
   * @param type $newPassword
   * @return boolean
   */
  public function changePassword($newPassword){
    if($this->id > 0 && strlen($newPassword) > 0){
      $sql = "UPDATE ".CFG_DBT_USERS." SET ".CFG_DBT_USERS.".`pwd` = PASSWORD('" . mysql_real_escape_string($newPassword) . "') WHERE ".CFG_DBT_USERS.".`id` = '" . mysql_real_escape_string($this->id) . "'";
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USERS);
      $mysql->setSQL($sql);
      $mysql->updateRow();
      if($mysql->hasAffectedRows()){
        return true;
      }
    }
    return false;
  }
  
  /**
   * Remove roles by its ids
   * @param array $roles Remove role ids: array(role_id1, role_id2)
   * @return boolean
   */
  public function removeRoles($roles = array()){
    if($this->id>0){
      if(is_array($roles) && count($roles)>0){
        $currentRoles = array();
        if(!isEmptyString($this->roles)){
          $currentRoles = explode(',', $this->roles);
        }

        $newRoles = array_diff($currentRoles, $roles);
        $this->roles = implode(',', $newRoles);

        if($this->save()){
          return true;
        }
      }
    }
    return false;
  }
  
  /**
   * Deletes a user from database if id > 1
   * Root user can not be deleted
   * @return boolean
   */
  public function delete(){
    if($this->id > 1){
      $mysql = new mysqlDatabase();
      $mysql->setTable(CFG_DBT_USERS);
      $mysql->setColumn('id');
      $mysql->setRestriction('id', '=', $this->id);
      
      $mysql->deleteRows();
      if($mysql->hasAffectedRows()){
        return true;
      }
    }
    
    return false;
  }
  
}
?>
