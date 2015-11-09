<?php
/**
 * iqz
 * Author: Arne Gockeln, WebSDK
 * Date: 03.11.15
 */

namespace WebSDK;

abstract class UserRightEnum {

    const MANAGE_USERS = 1;
    const MANAGE_OPTIONS = 2;

    static function toArray(){
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    static function getNames(){
        $names = array(
            1 => _('Benutzer verwalten'),
            2 => _('Einstellungen verwalten')
        );
        return $names;
    }

    static function getDescription(){
        $desc = array(
            1 => _('Der Benutzer darf andere Benutzer hinzufügen, bearbeiten und löschen.'),
            2 => _('Der Benutzer darf globale Einstellungen ändern.')
        );
        return $desc;
    }
}
