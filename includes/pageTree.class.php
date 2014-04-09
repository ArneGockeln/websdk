<?php
/*
 * Developed by Arne Gockeln.
 * Do not use this code in your own project without my permission!
 * Get more info on http://www.webchef.de
 */

class PageTree {
  private $tree = array();
  
  public function __construct(){
    unset($this->tree);
    $this->tree = array();
  }
   
  /**
   * Adds new element to tree
   * @param type $url blank url, without function getUrl!!
   * @param type $text link text
   * @param type $ident page ident, false if auto generate from $text
   * @param type $rights array with right elements
   * @param type $pos element position in tree, -1 equals to new element at the end
   * @param boolean $needLogin if true, it will be only rendered if we have an active session
   */
  public function add($url, $text, $ident = false, $rights = array(-1), $pos = -1, $needLogin = false) {
    // check for login requirement
    if($needLogin !== false){
      if(!UserSession::isOnline()){
        return false;
      }
    }
    
    if($url !== 'dropdown'){
      $element = array(
        'url' => getUrl($url), 
        'text' => $text,
        'ident' => ($ident !== false ? $ident : preg_replace('/[^\w\._]+/', '', strtolower($text))),
        'rights' => $rights
        );
    } else {
      $element = array(
          'url' => 'dropdown',
          'text' => $text,
          'elements' => array()
      );
    }
    
    // position
    if($pos !== -1){
      if(is_numeric($pos)){
        if(array_key_exists('elements', $this->tree[$pos])){
          $this->tree[$pos]['elements'][] = $element;
        } else {
          die(_('An dieser Position existiert kein Dropdown!'));
        }
      } else {
        die(_('Position muss numerisch sein!'));
      }
    } else {
      // new element at the end
      $this->tree[] = $element;
    }
  }
  
  /**
   * get tree
   * @return type array
   */
  public function get(){
    return $this->tree;
  }
  
  public function debug(){
    dump($this->tree);
  }
}
?>
