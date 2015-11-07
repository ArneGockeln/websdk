<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 07.11.15
 */

namespace WebSDK;


class TreeListItem
{
    private $id; // unique id
    private $parent_id; // parent id, defaults to null
    private $text; // string
    private $attributes; // array
    private $treeList; // reference to treeList

    /**
     * Add Child
     * @param TreeListItem $childTreeListItem
     * @return TreeListItem
     */
    public function add($childTreeListItem){
        $childTreeListItem->setParentId($this->getId());

        return $this->getTreeList()->add($childTreeListItem);
    }

    /**
     * Append text
     * @param string $content
     * @return TreeListItem $this
     */
    public function append($content){
        $this->text .= $content;

        return $this;
    }

    /**
     * Prepend text
     * @param string $content
     * @return TreeListItem $this
     */
    public function prepend($content){
        $this->text = $content . $this->text;

        return $this;
    }

    /**
     * Check if this item has children
     * @param TreeList $treeList
     * @return bool
     */
    public function hasChildren(&$treeList){
        if(($treeList instanceof TreeList)){
            return count($treeList->getWithParent($this->getId())) > 0;
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
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes)
    {
        // parse key/value pairs
        if(is_array($attributes)) {
            foreach($attributes as $key => $value){
                switch(strtolower($key)){
                    case 'id':
                        $this->setId($value);
                        unset($attributes['id']);
                        break;
                    case 'text':
                        $this->setText($value);
                        unset($attributes['text']);
                        break;
                    case 'parent_id':
                        $this->setParentId($value);
                        unset($attributes['parent_id']);
                        break;
                }
            }

            $this->attributes = $attributes;
        }
    }

    /**
     * @return TreeList
     */
    protected function getTreeList()
    {
        return $this->treeList;
    }

    /**
     * @param TreeList $treeList
     */
    public function setTreeList($treeList)
    {
        if(($treeList instanceof TreeList)){
            $this->treeList = $treeList;
        }
    }
}