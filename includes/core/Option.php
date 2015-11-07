<?php
/**
 * WebSDK Options
 * Author: Arne Gockeln, WebSDK
 * Date: 16.10.15
 */

namespace WebSDK;

class Option implements IDBObject
{
    private $id;
    private $option_category;
    private $option_key;
    private $option_value;

    public function __construct($id = 0){
        if($id > 0){
            $this->setId($id);
            $this->load();
        }
    }

    public function load()
    {
        if($this->getId()>0){
            restoreFromDB(Database::getRowOf(DBTables::OPTIONS, $this->getId()), $this);
        }
    }

    public function save()
    {
        if($this->getId()>0){
            return Database::updateRowWithID(DBTables::OPTIONS, prepareForDB($this), $this->getId());
        } else {
            $this->setId(
                Database::insertRow(DBTables::OPTIONS, prepareForDB($this))
            );
            return $this->getId()>0;
        }
    }

    public function delete()
    {
        if($this->getId() > 0){
            return Database::deleteRowWithID(DBTables::OPTIONS, $this->getId());
        }
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
     * @return mixed
     */
    public function getOptionCategory()
    {
        return $this->option_category;
    }

    /**
     * @param mixed $option_category
     */
    public function setOptionCategory($option_category)
    {
        $this->option_category = $option_category;
    }

    /**
     * @return mixed
     */
    public function getOptionKey()
    {
        return $this->option_key;
    }

    /**
     * @param mixed $option_key
     */
    public function setOptionKey($option_key)
    {
        $this->option_key = $option_key;
    }

    /**
     * @return mixed
     */
    public function getOptionValue()
    {
        return $this->option_value;
    }

    /**
     * @param mixed $option_value
     */
    public function setOptionValue($option_value)
    {
        $this->option_value = $option_value;
    }
}