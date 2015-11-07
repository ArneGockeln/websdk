<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 07.11.15
 */

namespace WebSDK;


class BootstrapListItem extends RouteListItem
{
    /**
     * Overwrite add method with added attributes on child items
     * @param TreeListItem $childListItem
     * @return TreeListItem
     */
    public function add($childTreeListItem){
        $childTreeListItem->setParentId($this->getId());
        $childTreeListItem->setAttributes(array('class' => 'dropdown'));

        return $this->getTreeList()->add($childTreeListItem);
    }

    /**
     * Overwrite getLink() with dropdown
     * @param bool|false $isDropdown
     * @return string
     */
    public function getLink($isDropdown = false){
        if($isDropdown){
            return '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$this->getText().' <span class="caret"></span></a>';
        }

        return parent::getLink();
    }
}