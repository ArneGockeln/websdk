<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 07.11.15
 */

namespace WebSDK;


class BootstrapNavbar extends TreeList
{
    /**
     * Overwrite to match Twitter Bootstrap Navbar
     * @param string $type
     * @param null $parent_id
     * @return string
     */
    public function render($type = 'ul', $parent_id = null){
        $html = '';
        $element = in_array($type, ['ul', 'ol']) ? 'li' : $type;

        foreach($this->getWithParent($parent_id) as $item){
            if(($item instanceof RouteListItem)){
                $hasChildren = $item->hasChildren($this);

                $parsedAttr = $this->parseAttributes($item->getAttributes());
                if(isActiveUri($item->getRoute())){
                    // check if we have a class attribute
                    $classAttr = getValue($item->getAttributes(), 'class');
                    if(!is_empty($classAttr)){
                        $parsedAttr = preg_replace('/class="[^"]*/', '\0 active', $parsedAttr);
                    } else {
                        $parsedAttr .= ' class="active"';
                    }
                }

                $html .= '<' . $element . $parsedAttr . '>';
                // text|link|etc
                $html .= $item->getLink($hasChildren);
                // children
                if($hasChildren){
                    $html .= '<' . $type . ' class="dropdown-menu">';
                    $html .= $this->render($type, $item->getId());
                    $html .= '</' . $type . '>';
                }
                $html .= '</' . $element . '>';
            }
        }
        return $html;
    }
}