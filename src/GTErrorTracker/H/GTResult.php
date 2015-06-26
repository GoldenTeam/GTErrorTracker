<?php

namespace GTErrorTracker\H;

/**
 * Description of Result
 * @author kalin-mv
 */
class GTResult {
    private static $_error = true;
    private static $_message = "";
    private static $_code = 0;
    
    public static function isError() { return self::$_error; }
    public static function getMessage() { return self::$_message; }
    public static function getCode() { return self::$_code; }

    public static function to($message = null, $error = true, $code = 0) {
        if (is_array($message)) {
            self::$_message = isset($message["message"])?$message["message"]:"Unexpected error with result!";
            self::$_error = isset($message["error"])?$message["error"]:true;
            self::$_code = isset($message["code"])?$message["code"]:true;
        } else if ($message !== null) {
            self::$_message = $message;
            self::$_error = $error;
            self::$_code = $code;
        }
        return array("error" => self::$_error, "message" => self::$_message, "code" => self::$_code);
    }
}
