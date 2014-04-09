<?php

/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

class UserRole {
  public $id;
  public $name;
  public $rights;
  
  private $table = CFG_DBT_USER_ROLES;
  
  public function __construct($id = 0) {
    if($id > 0){
      $this->id = $id;
      $this->load($id);
    }
  }
  
  public function load($id){
    if($id>0){
      $mysql = new mysqlDatabase($this->table);
      $mysql->setColumns('*');
      $mysql->setRestriction('id', '=', $id);
      
      $ret = $mysql->fetchRow();
      if($mysql->hasRows()){
        $this->id = $ret->id;
        $this->name = $ret->name;
        $this->rights = $ret->rights;
      }
    }
  }
  
  public function save(){
    $mysql = new mysqlDatabase($this->table);
    
    if($this->id>0){
      $mysql->setColumn('id', $this->id);
    }
    
    $mysql->setColumns(array(
        'name' => $this->name,
        'rights' => $this->rights
    ));
    
    $mysql->setRestriction('id', '=', $this->id);
    
    $mysql->updateRow();
    if($mysql->hasAffectedRows()){
      if($this->id <= 0){
        $this->id = $mysql->last_insert_id;
      }
      
      return true;
    }
    return false;
  }
  
  public function delete(){
    if($this->id>1){
      $mysql = new mysqlDatabase($this->table);
      $mysql->setColumn('id');
      $mysql->setRestriction('id', '=', $this->id);
      $mysql->deleteRows();
      if($mysql->hasAffectedRows()){
        return true;
      }
    }
    return false;
  }
  
  /**
   * Remove rights by its ids
   * @param array $rights Remove rights ids: array(right_id1, right_id2)
   * @return boolean
   */
  public function removeRights($rights = array()){
    if($this->id>0){
      if(is_array($rights) && count($rights)>0){
        $currentRights = array();
        if(!isEmptyString($this->rights)){
          $currentRights = explode(',', $this->rights);
        }

        $newRights = array_diff($currentRights, $rights);
        $this->rights = implode(',', $newRights);

        if($this->save()){
          return true;
        }
      }
    }
    return false;
  }
}
?>
