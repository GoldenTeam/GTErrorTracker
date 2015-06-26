<?php
/**
 * Created by PhpStorm.
 * User: melnik-da
 * Date: 6/17/15
 * Time: 9:28 AM
 */

namespace GTErrorTracker\H;

class EventType {

    const EXCEPTION_DISPATCH = 1;
    const EXCEPTION_RENDER = 2;
    const ERROR_PHP = 3;
    const EXCEPTION_PHP = 4;
    const WARNING_PHP = 5;
    const NOTICE_PHP = 6;

    static public function toArray() {
        return array(
            self::EXCEPTION_DISPATCH => array(
                "id" => self::EXCEPTION_DISPATCH,
                "name" => "EXCEPTION_DISPATCH",
                "descriptions" => "EVENT_DISPATCH_ERROR Zend onBootstrap",
                "cssClass" => "warning",
            ),

            self::EXCEPTION_RENDER => array(
                "id" => self::EXCEPTION_RENDER,
                "name" => "EXCEPTION_RENDER",
                "descriptions" => "ERROR_RENDER Zend onBootstrap",
                "cssClass" => "warning",
                ),

            self::ERROR_PHP => array(
                "id" => self::ERROR_PHP,
                "name" => "ERROR_PHP",
                "descriptions" => "ERROR_PHP",
                "cssClass" => "error",
                ),

            self::WARNING_PHP => array(
                "id" => self::WARNING_PHP,
                "name" => "WARNING_PHP",
                "descriptions" => "WARNING_PHP",
                "cssClass" => "warning",
            ),

            self::NOTICE_PHP => array(
                "id" => self::WARNING_PHP,
                "name" => "NOTICE_PHP",
                "descriptions" => "NOTICE_PHP",
                "cssClass" => "success",
            ),

            self::EXCEPTION_PHP => array(
                "id" => self::EXCEPTION_PHP,
                "name" => "EXCEPTION_PHP",
                "descriptions" => "EXCEPTION_PHP",
                "cssClass" => "warning",
                ),
            );
    }

    public static function get($item_id, $name) {
        $result = "";
        $array = self::toArray();
        if (array_key_exists($item_id, $array)) {
            $result = isset($array[$item_id][$name]) ? $array[$item_id][$name] : "";
        }
        return $result;
    }

    public static function getName($key) {
        return self::get($key, "name");
    }
}