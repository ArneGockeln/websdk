<?php
/**
 * iqz
 * Author: Arne Gockeln, WebSDK
 * Date: 16.10.15
 */

namespace WebSDK;


class Menu
{
    private $menu;

    public function __construct(){
        $this->setMenu(array());
    }

    public function addElement($config = array()){
        try {
            if(!is_array($config)) throw new \Exception(_('Parameter ist kein Array!'));

            $element = array(
                'route' => getValue($config, 'route'),
                'text' => getValue($config, 'text'),
                'isActive' => isActiveUri(getValue($config, 'route'))
            );
            // icon?
            if(array_key_exists('icon', $config)){
                $element['icon'] = getValue($config, 'icon');
            }
            // parent?
            if(array_key_exists('parent', $config)){
                foreach($this->menu as $route => $level0){
                    if($route == getValue($config, 'parent')){
                        $level0['childs'][getValue($config, 'route')] = $element;
                    }

                    if(array_key_exists('childs', $level0)) {
                        $this->findParent($level0['childs'], getValue($config, 'parent'), $element);
                    }
                }
            } else {
                $this->menu[getValue($config, 'route')] = $element;
            }
        } catch(\Exception $e){
            debug($e->getMessage(), true);
        }
    }

    private function findParent($nextLevel, $parentRoute, $menu = array()){
        foreach($nextLevel as $route => $currentItem){
            if($route == $parentRoute){
                $currentItem['childs'][getValue($menu, 'route')] = $menu;
            }

            if(array_key_exists('childs', $currentItem)) {
                $this->findParent($currentItem['childs'], $parentRoute, $menu);
            }
        }
    }

    /**
     * Add route element
     * @param string $route
     * @param string $text
     * @param string $icon
     * @throws \Exception
     */
    public function add($route, $text, $icon = ''){
        if(is_empty($route) || !isSecureString($route)){
            throw new \Exception(_('Menu: ROUTE is empty!'));
        }

        if(is_empty($text) || !isSecureString($text)){
            throw new \Exception(_('Menu: TEXT is empty!'));
        }

        $this->menu[] = array(
            'route' => $route,
            'text' => (!is_empty($icon) ? '<i class="fa ' . $icon . '"></i> ' : '') . $text,
            'isActive' => isActiveUri($route)
        );
    }

    /**
     * @return mixed
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param mixed $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    public function debug(){
        debug($this->getMenu());
    }
}