<?php
/**
 * Author: Arne Gockeln, WebSDK
 * Date: 18.09.15
 */

namespace WebSDK;


abstract class UserTypeEnum
{
    const ADMINISTRATOR = 0;
    const USER = 1;

    static function toArray(){
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    static function getNames(){
        $names = array(
            _('Administrator'), // 0
            _('Benutzer') // 1
        );
        return $names;
    }
}