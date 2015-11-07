<?php
/**
 * websdk
 * Author: Arne Gockeln, Webchef
 * Date: 07.11.15
 */

namespace WebSDK;


class RouteListItem extends TreeListItem
{
    private $route;

    public function __construct($route, $text, $attributes = array()){
        $this->setRoute($route);
        $this->setText($text);
        $this->setAttributes($attributes);
    }

    public function getLink(){
        $route = $this->getRoute();
        if($route != '#'){
            $route = getHttpHost(false) . $this->getRoute();
        }
        return '<a href="' . $route . '"'.$this->getTreeList()->parseAttributes($this->getAttributes()).'>' . $this->getText() . '</a>';
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
}