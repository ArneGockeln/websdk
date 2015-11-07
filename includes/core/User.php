<?php
/**

 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

namespace WebSDK;


class User implements IDBObject
{
    private $id;
    private $type; // 0 = administrator, 1 = user
    private $firstname;
    private $lastname;
    private $username;
    private $email;
    private $locale;
    private $rights;
    private $pwd;
    private $salt;
    private $locked;
    private $deleted;
    private $lastmod;

    public function __construct( $id = 0 ){
        if($id > 0){
            $this->setId($id);
            $this->load();
        }
    }

    public function save()
    {
        if($this->getId() > 0){
            return Database::updateRowWithID(DBTables::USERS, prepareForDB($this), $this->getId());
        } else if(!is_null($this->getUsername()) && strlen($this->getUsername()) > 0 && strlen($this->getPwd()) > 0){
            $this->setId(
                Database::insertRow(DBTables::USERS, prepareForDB($this))
            );
            return $this->getId() > 0;
        } else {
            return false;
        }
    }

    public function load()
    {
        $mysql = Database::getInstance();
        $sql = "SELECT * FROM " . DBTables::USERS . " WHERE ";
        if($this->getId() > 0){
            $sql .= " id = '" . $this->getId() . "'";
        } else if(!is_null($this->getUsername()) && strlen($this->getUsername()) > 0) {
            $sql .= " username = '" . $mysql->getEscapedString($this->getUsername()) . "'";
        }

        restoreFromDB($mysql->query($sql)->fetchRow(true), $this);
    }

    public function delete()
    {
        if($this->getId() > 0){
            $this->setDeleted(1);
            return $this->save();
        }
        return false;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return UserTypeEnum
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param UserTypeEnum $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }


    /**
     * @return mixed
     */
    public function getRights()
    {
        return $this->rights;
    }

    /**
     * @param mixed $rights
     */
    public function setRights($rights)
    {
        $this->rights = $rights;
    }

    /**
     * @return mixed
     */
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * Changes password
     * @param string $pwd
     */
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param mixed $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return mixed
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param mixed $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param mixed $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getLastmod()
    {
        return $this->lastmod;
    }

    /**
     * @param mixed $lastmod
     */
    public function setLastmod($lastmod)
    {
        $this->lastmod = $lastmod;
    }


}