<?php
/**

 * Author: Arne Gockeln, WebSDK
 * Date: 23.08.15
 */

namespace WebSDK;

/**
 * Interface DBObject
 *
 * This interface is used by objects that interacts with the database
 * @package PlainBugs
 */
interface IDBObject
{
    public function load();
    public function save();
    public function delete();
}