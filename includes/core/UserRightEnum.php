<?php
/**
 * iqz
 * Author: Arne Gockeln, WebSDK
 * Date: 03.11.15
 */

namespace WebSDK;

abstract class UserRightEnum {

    const MANAGE_USERS = 1;
    const MANAGE_EVENTS = 2;
    const MANAGE_OPTIONS = 3;

    static function toArray(){
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    static function getNames(){
        $names = array(
            1 => _('Benutzer verwalten'),
            2 => _('Seminare verwalten'),
            3 => _('Einstellungen verwalten')
        );
        return $names;
    }

    static function getDescription(){
        $desc = array(
            1 => _('Der Benutzer darf andere Benutzer hinzufügen, bearbeiten und löschen.'),
            2 => _('Der Benutzer darf Seminare hinzufügen, bearbeiten und löschen'),
            3 => _('Der Benutzer darf globale Einstellungen ändern.')
        );
        return $desc;
    }
}
