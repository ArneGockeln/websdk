<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 07.11.15
 */

namespace WebSDK;

class TreeList
{
    /**
     * @var array
     */
    private $list;
    /**
     * @var TreeListItem
     */
    private $lastListItem;


    function __construct()
    {
        $this->list = array();
    }

    /**
     * Returns listItems at the given level
     * @param null $parent_id
     * @return array
     */
    public function getWithParent($parent_id = null){
        return array_filter($this->list, function($listItem) use($parent_id){
            if($listItem->getParentId() == $parent_id){
                return true;
            }
            return false;
        });
    }

    /**
     * Filters items based on a callable
     * @param callable $closure
     */
    public function filter($closure){
        if(is_callable($closure)){
            $this->list = array_filter($this->list, $closure);
        }
    }

    /**
     * Adds a treelistitem
     * @param TreeListItem $treeListItem
     */
    public function add($treeListItem){
        $treeListItem->setId($this->obtainId());
        $treeListItem->setTreeList($this);
        $this->list[] = $treeListItem;

        return $treeListItem;
    }

    /**
     * Renders TreeList as ul or ol
     * @param string $type ul or ol
     * @param null|int $parent_id the parent id if we are in children mode
     * @return string
     */
    public function render($type = 'ul', $parent_id = null){
        $html = '';
        $element = in_array($type, ['ul', 'ol']) ? 'li' : $type;

        foreach($this->getWithParent($parent_id) as $item){
            if(($item instanceof TreeListItem)){
                $html .= '<' . $element . $this->parseAttributes($item->getAttributes()) . '>';

                $html .= $item->getText();

                if($item->hasChildren($this)){
                    $html .= '<' . $type . '>';
                    $html .= $this->render($type, $item->getId());
                    $html .= '</' . $type . '>';
                }
                $html .= '</' . $element . '>';
            }
        }
        return $html;
    }

    /**
     * Return list as UL
     * @param array $attributes
     * @return string
     */
    public function renderAsUL($attributes = array()){
        return '<ul' . $this->parseAttributes($attributes) . '>' . $this->render('ul') . '</ul>';
    }

    /**
     * Return list as OL
     * @param array $attributes
     * @return string
     */
    public function renderAsOL($attributes = array()){
        return '<ol' . $this->parseAttributes($attributes) . '>' . $this->render('ol') . '</ol>';
    }

    /**
     * Parse attributes to html attributes
     * @param array $attributes
     * @return string
     */
    public function parseAttributes($attributes = array()){
        $html = array();
        foreach($attributes as $key => $value){
            if(is_numeric($key)){
                $key = $value;
            }

            $element = (!is_null($value)) ? $key . '="' . $value . '"' : null;

            if(!is_null($element)) $html[] = $element;
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Length of the list
     * @return int
     */
    public function length(){
        return count($this->list);
    }

    /**
     * Obtain an id
     * @return int
     */
    private function obtainId(){
        return $this->length() + 1;
    }

    /**
     * debug
     */
    public function debug(){
        debug($this->list);
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * @return mixed
     */
    public function getLastListItem()
    {
        return $this->lastListItem;
    }

    /**
     * @param mixed $lastListItem
     */
    public function setLastListItem($lastListItem)
    {
        $this->lastListItem = $lastListItem;
    }
}