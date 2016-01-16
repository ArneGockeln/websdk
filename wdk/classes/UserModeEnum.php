<?php
/**
 * Author: Arne Gockeln, Webchef
 * Date: 19.11.15
 */

namespace WebSDK;


class UserModeEnum
{
    const PROFILE = 0;
    const USER = 1;

    static function toArray(){
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}