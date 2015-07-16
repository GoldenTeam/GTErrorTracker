<?php
define('DISPLAY_ERRORS', TRUE);
define('ERROR_REPORTING', E_ALL | E_STRICT);

use GTErrorTracker\H\EventType;
use GTErrorTracker\H\ServiceLocatorFactory;
use GTErrorTracker\Model\EventLogger;

set_error_handler('process_error_backtrace');
set_exception_handler('process_exception_backtrace');
register_shutdown_function('fatal_error_handler');

function fatal_error_handler() {
    // if error exists and it is fatal
    if ($error = error_get_last() AND $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
        // Clean buffer (do not report standard error message)
        //ob_end_clean();
        // running error handler
        $errcontext = "errcontext is not available after (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)";
        process_error_backtrace($error['type'], $error['message'], $error['file'], $error['line'], $errcontext);
    } else {
        // sending (output) buffer and its turning off
        //ob_end_flush();
    }
}

function process_exception_backtrace($exception) {
    $sm = ServiceLocatorFactory::getInstance()->getServiceLocator();
    $customEvent = new EventLogger($sm);
    $customEvent->set_event_type(EventType::EXCEPTION_PHP);
    $customEvent->handle($exception);
    die();
}


function grab_dump($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}

function process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    $sm = ServiceLocatorFactory::getInstance()->getServiceLocator();
    $customEvent = new EventLogger($sm);

    $variablesDump = grab_dump($errcontext);


    switch ($errno) {
        case E_ERROR             :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_WARNING           :    $errorDangerLevel = EventType::WARNING_PHP;  break;
        case E_PARSE             :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_NOTICE            :    $errorDangerLevel = EventType::NOTICE_PHP;   break;
        case E_CORE_ERROR        :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_CORE_WARNING      :    $errorDangerLevel = EventType::WARNING_PHP;  break;
        case E_COMPILE_ERROR     :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_COMPILE_WARNING   :    $errorDangerLevel = EventType::WARNING_PHP;  break;
        case E_USER_ERROR        :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_USER_WARNING      :    $errorDangerLevel = EventType::WARNING_PHP;  break;
        case E_USER_NOTICE       :    $errorDangerLevel = EventType::NOTICE_PHP;   break;
        case E_STRICT            :    $errorDangerLevel = EventType::WARNING_PHP;  break;
        case E_RECOVERABLE_ERROR :    $errorDangerLevel = EventType::ERROR_PHP;    break;
        case E_DEPRECATED        :    $errorDangerLevel = EventType::NOTICE_PHP;   break;
        case E_USER_DEPRECATED   :    $errorDangerLevel = EventType::NOTICE_PHP;   break;
        default                  :    $errorDangerLevel = EventType::ERROR_PHP;    break;
    }

    $customEvent->set_event_type($errorDangerLevel);
    $trace = debug_backtrace();
    array_shift($trace);
    $customEvent->handle($errno, $errstr, $errfile, $errline, $trace, $variablesDump);

    if ($errorDangerLevel == EventType::ERROR_PHP) {
        die;
    }
}

/* http://php.net/manual/en/errorfunc.constants.php

1	E_ERROR (integer)
Fatal run-time errors.
These indicate errors that can not be recovered from, such as a memory allocation problem.
Execution of the script is halted.

2	E_WARNING (integer)
Run-time warnings (non-fatal errors).
Execution of the script is not halted.

4	E_PARSE (integer)
Compile-time parse errors.
Parse errors should only be generated by the parser.

8	E_NOTICE (integer)
Run-time notices.
Indicate that the script encountered something that could indicate an error,
but could also happen in the normal course of running a script.

16	E_CORE_ERROR (integer)
Fatal errors that occur during PHP's initial startup.
This is like an E_ERROR, except it is generated by the core of PHP.

32	E_CORE_WARNING (integer)
Warnings (non-fatal errors) that occur during PHP's initial startup.
This is like an E_WARNING, except it is generated by the core of PHP.

64	E_COMPILE_ERROR (integer)
Fatal compile-time errors.
This is like an E_ERROR, except it is generated by the Zend Scripting Engine.

128	E_COMPILE_WARNING (integer)
Compile-time warnings (non-fatal errors).
This is like an E_WARNING, except it is generated by the Zend Scripting Engine.

256	E_USER_ERROR (integer)
User-generated error message.
This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error().

512	E_USER_WARNING (integer)
User-generated warning message.
This is like an E_WARNING, except it is generated in PHP code by using the PHP function trigger_error().

1024	E_USER_NOTICE (integer)
User-generated notice message.
This is like an E_NOTICE, except it is generated in PHP code by using the PHP function trigger_error().

2048	E_STRICT (integer)
Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code.
Since PHP 5 but not included in E_ALL until PHP 5.4.0

4096	E_RECOVERABLE_ERROR (integer)
Catchable fatal error.
It indicates that a probably dangerous error occurred, but did not leave the Engine in an unstable state.
If the error is not caught by a user defined handle (see also set_error_handler()), the application aborts as it was an E_ERROR.
Since PHP 5.2.0

8192	E_DEPRECATED (integer)
Run-time notices. Enable this to receive warnings about code that will not work in future versions.
Since PHP 5.3.0

16384	E_USER_DEPRECATED (integer)
User-generated warning message.
This is like an E_DEPRECATED, except it is generated in PHP code by using the PHP function trigger_error().
Since PHP 5.3.0

32767	E_ALL (integer)
All errors and warnings, as supported, except of level E_STRICT prior to PHP 5.4.0.
32767 in PHP 5.4.x, 30719 in PHP 5.3.x, 6143 in PHP 5.2.x, 2047 previously

*/

