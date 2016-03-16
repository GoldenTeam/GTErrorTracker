<?php

namespace GTErrorTracker\Model;

use GTErrorTracker\H;
use GTErrorTracker\H\EventType;
use GTErrorTracker\H\ServiceLocatorFactory;

use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Mvc\Application;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

class EventLogger extends GTBaseEntity {

    private $_f_event_logger_id = null;
    private $_f_event_file = null;
    private $_f_line = null;
    private $_f_event_code = null;
    private $_f_message = null;
    private $_f_stack_trace = null;
    private $_f_event_type = null;
    private $_f_date_time = null;
    private $_f_user_id = null;
    private $_f_device_id = null;
    private $_f_event_hash = null;
    private $_f_xdebug_message = null;
    private $_f_variables_dump = null;
    private $_f_trace_dump = null;
    private $_f_ip_address = null;

    private $_default_error_message = "Some Error Occurred. Please Contact to the Administrator, error code = ";
    private $_result = array('error' => true);
    private $_customConfig = array();
    private $_headerSignKey;
    private $_headerToken;

    protected $serviceManager;

    const ERROR_CODE_RESPONSE = 0;
    const SECONDS_PREVENT_ERROR_RECURSION = 2;

    /**
     * User
     * @var GTUserInterface
     */
    private $_user = null;

    /**
     * @return GTUserInterface
     */
    public function get_user() {

        if (!$this->_user && $this->get_user_id() > 0) {
            $hasGTUserGateway = ServiceLocatorFactory::getInstance()->getServiceLocator()->has('gt_user_gateway');
            if ($hasGTUserGateway) {
                $sets = ServiceLocatorFactory::getInstance()->getServiceLocator()->get('gt_user_gateway');
                $user = $sets->findById($this->get_user_id());
                if ($user instanceof GTUserInterface) {
                    $this->_user = $user;
                }
            }
        }
        return $this->_user;
    }

    public function get_event_logger_id() { return $this->_f_event_logger_id; }
    public function get_event_file()      { return $this->_f_event_file; }
    public function get_line()            { return $this->_f_line; }
    public function get_event_code()      { return $this->_f_event_code; }
    public function get_message()         { return $this->_f_message; }
    public function get_stack_trace()     { return $this->_f_stack_trace; }
    public function get_event_type()      { return $this->_f_event_type; }
    public function get_user_id()         { return $this->_f_user_id; }
    public function get_device_id()       { return $this->_f_device_id; }
    public function get_date_time()       { return $this->_f_date_time; }
    public function get_event_hash()      { return $this->_f_event_hash; }
    public function get_xdebug_message()  { return $this->_f_xdebug_message; }
    public function get_variables_dump()  { return $this->_f_variables_dump; }
    public function get_trace_dump()      { return $this->_f_trace_dump; }
    public function get_ip_address()      { return $this->_f_ip_address; }

    public function set_event_logger_id($event_logger_id) { $this->_f_event_logger_id = $event_logger_id; return $this; }
    public function set_event_file($file)                 { $this->_f_event_file = $file; return $this; }
    public function set_line($line)                       { $this->_f_line = $line; return $this; }
    public function set_event_code($code)                 { $this->_f_event_code = $code; return $this; }
    public function set_message($message)                 { $this->_f_message = $message; return $this; }
    public function set_stack_trace($stack_trace)         { $this->_f_stack_trace = $stack_trace; return $this; }
    public function set_event_type($event_type)           { $this->_f_event_type = $event_type; return $this; }
    public function set_user_id($user_id)                 { $this->_f_user_id = $user_id; return $this; }
    public function set_device_id($device_id)             { $this->_f_device_id = $device_id; return $this; }
    public function set_date_time($date)                  { $this->_f_date_time = $date; return $this; }
    public function set_event_hash($event_hash)           { $this->_f_event_hash = $event_hash; return $this; }
    public function set_xdebug_message($xdebug_message)   { $this->_f_xdebug_message = $xdebug_message; return $this; }
    public function set_variables_dump($variables_dump)   { $this->_f_variables_dump = $variables_dump; return $this; }
    public function set_trace_dump($trace_dump)           { $this->_f_trace_dump = $trace_dump; return $this; }
    public function set_ip_address($ip_address)           { $this->_f_ip_address = $ip_address; return $this; }


    function __construct($_serviceLocator = null) {
        parent::__construct($_serviceLocator);

        $this->_f_date_time = H\Env::getDateTime()->getTimestamp();

        $session = new Container('mobile');
        $this->_f_device_id = isset($session->device_id) ? $session->device_id : null;

    }

    public function getCssClasses() {
        return EventType::get($this->_f_event_type, "cssClass");
    }

    public function save() {
        $EG = $this->gateway('EventLoggerGateway');
        return $EG->save($this);
    }

    public function delete()  {
        $EG = $this->gateway('EventLoggerGateway');
        $affected = $EG->remove($this);
        if ($affected > 0) {
            $this->set_event_logger_id(null);
        }
    }

    public function grab_dump($var)
    {
        ob_start();
        var_dump($var);
        return ob_get_clean();
    }

    public function handle() {
        $args = func_get_arg(0);
        $serviceManager = ServiceLocatorFactory::getInstance()->getServiceLocator();
        $config = $serviceManager->get('config');
        $headers = $serviceManager->get('request')->getHeaders();
        $this->_customConfig = $config["GTErrorTracker"];
        $this->_customConfig['errorCodeResponse'] = $this->_customConfig['errorCodeResponse'] ?
            $this->_customConfig['errorCodeResponse'] : self::ERROR_CODE_RESPONSE;
        $this->_customConfig['secondsPreventErrorRecursion'] = $this->_customConfig['secondsPreventErrorRecursion'] ?
            $this->_customConfig['secondsPreventErrorRecursion'] : self::SECONDS_PREVENT_ERROR_RECURSION;

        $this->_headerSignKey = $headers->get('Signkey');
        $this->_headerToken = $headers->get('Token');

        if ($args instanceof \Exception) {
            $this->_f_event_file = $args->getFile();
            $this->_f_message = $args->getMessage();
            $this->_f_line = $args->getLine();
            $this->_f_event_code = "Exception:" . $args->getCode();
            $this->_f_stack_trace = $this->stackTraceProcessing($args->getTrace(), $args->getMessage());

            if (isset($args->xdebug_message)) {
                $this->_f_xdebug_message = $args->xdebug_message;
            }

        } else {
            //Arguments Order
            //$errno, $errstr, $errfile, $errline, $trace
            $args = func_get_args();
            $errno = $args[0];
            $errstr = $args[1];
            $this->_f_event_file = $args[2];
            $this->_f_line = $args[3];
            $trace = $args[4]; // trace array
            $errcontext = $args[5]; // variables value near error
            $route = isset($args[6]) ? $args[6] : null; //wrong route on 404 page

            $type = "Undefined";

            switch ($errno) {
                case E_ERROR            :
                    $type = "E_ERROR";
                    break;
                case E_WARNING          :
                    $type = "E_WARNING";
                    break;
                case E_PARSE            :
                    $type = "E_PARSE";
                    break;
                case E_NOTICE           :
                    $type = "E_NOTICE";
                    break;
                case E_CORE_ERROR       :
                    $type = "E_CORE_ERROR";
                    break;
                case E_CORE_WARNING     :
                    $type = "E_CORE_WARNING";
                    break;
                case E_COMPILE_ERROR    :
                    $type = "E_COMPILE_ERROR";
                    break;
                case E_COMPILE_WARNING  :
                    $type = "E_COMPILE_WARNING";
                    break;
                case E_USER_ERROR       :
                    $type = "E_USER_ERROR";
                    break;
                case E_USER_WARNING     :
                    $type = "E_USER_WARNING";
                    break;
                case E_USER_NOTICE      :
                    $type = "E_USER_NOTICE";
                    break;
                case E_STRICT           :
                    $type = "E_STRICT";
                    break;
                case E_RECOVERABLE_ERROR:
                    $type = "E_RECOVERABLE_ERROR";
                    break;
                case E_DEPRECATED       :
                    $type = "E_DEPRECATED";
                    break;
                case E_USER_DEPRECATED  :
                    $type = "E_USER_DEPRECATED";
                    break;
                case Application::ERROR_CONTROLLER_NOT_FOUND:
                    $type = "ERROR_CONTROLLER_NOT_FOUND";
                    break;
                case Application::ERROR_CONTROLLER_INVALID:
                    $type = "ERROR_CONTROLLER_INVALID";
                    break;
                case Application::ERROR_ROUTER_NO_MATCH:
                    $type = "ERROR_ROUTER_NO_MATCH";
                    break;
                case Application::ERROR_CONTROLLER_CANNOT_DISPATCH:
                    $type = "ERROR_CONTROLLER_CANNOT_DISPATCH";
                    break;
            }
            $this->_f_event_code = $type;
            if ($this->_f_event_type == EventType::ROUTER_NOT_MATCH) {
                $this->_f_message = "$errstr at route: $route";
            } else {
                $this->_f_message = "Backtrace from $this->_f_event_code $errstr at $this->_f_event_file $this->_f_line ";
            }
            $this->_f_stack_trace = $this->stackTraceProcessing($trace, $this->_f_message);

        }
        $remote = new RemoteAddress();
        $this->_f_ip_address = $remote->getIpAddress();

        $hasGTCurrentUser = ServiceLocatorFactory::getInstance()->getServiceLocator()->has('gt_current_user');
        if ($hasGTCurrentUser) {
            $user = ServiceLocatorFactory::getInstance()->getServiceLocator()->get('gt_current_user');
            if ($user instanceof GTUserInterface) {
                $this->_f_user_id = $user->getId();
            }
        }

        $event_hash = $this->getHash();
        $session = new Container('user');

        if($session->eventHash == $event_hash) {

            if($this->_f_date_time - $session->errorTime < $this->_customConfig['secondsPreventErrorRecursion']) {

                $this->echoIfDevMode($session->lastEventId);

            } else {

                $session->errorTime = $this->_f_date_time;         // save time when error has been occurred
                $this->redirectIfDevMode($session->lastEventId);
            }

        } else {
            $this->_f_event_hash = $event_hash;
            if ($this->_customConfig["GTErrorTypesSaveToDb"][H\EventType::getName($this->_f_event_type)]) {
                $this->save();
                $session->eventHash = $event_hash;                 // save new Hash to session
                $session->lastEventId = $this->_f_event_logger_id; // save new ID to session
                $session->errorTime = $this->_f_date_time;         // save time when error has been occurred
                $this->redirectIfDevMode($session->lastEventId);
            }
        }
    }

    public function getHash() {
        return md5($this->_f_stack_trace . $this->_f_event_file . $this->_f_event_code . $this->_f_message . $this->_f_event_type);
    }

    private function echoIfDevMode($event_logger_id) {
        if ($this->_customConfig['developmentMode']) {
            $html = 'Event Id:' . $event_logger_id . '<br>';
            $html .= H\EventType::getName($this->_f_event_type) . '<br>';
            $html .= $this->_f_event_code . '<br>';
            $html .= $this->_f_event_file . ':' . $this->_f_line . '<br>';
            $html .= $this->_f_message . '<br>';
            $html .= $this->_f_stack_trace . '<br>';
            $html .= 'User Id:' . $this->_f_user_id . '<br>';
            $html .= 'Device Id:' . $this->_f_device_id . '<br>';
            if (!$this->_headerSignKey && !$this->_headerToken) {
                echo $html;
            } else {
                $this->_result['result'] = H\GTResult::to($this->_default_error_message, true, $this->_customConfig['errorCodeResponse']);
                $this->_result['result'] = array(
                    'stack_trace' => $html,
                    'exception_message' => $this->_f_message,
                    'datetime' => $this->_f_date_time,
                    'message' => $this->_default_error_message . "$event_logger_id, $this->_f_message",
                    'code' => 3
                );
                $json = json_encode($this->_result);
                echo $json;
            }
        } else {
            if ($this->_f_event_type == EventType::ERROR_PHP ||
                $this->_f_event_type == EventType::EXCEPTION_DISPATCH ||
                $this->_f_event_type == EventType::EXCEPTION_RENDER ||
                $this->_f_event_type == EventType::EXCEPTION_PHP) {
                if (!$this->_headerSignKey && !$this->_headerToken) {
                    echo "<h1>$this->_default_error_message . $event_logger_id</h1>";
                } else {
                    $this->_result['result'] = H\GTResult::to($this->_default_error_message . $event_logger_id, true, $this->_customConfig['errorCodeResponse']);
                    $json = json_encode($this->_result);
                    echo $json;
                }
                die;
            }
            if ($this->_f_event_type == EventType::ROUTER_NOT_MATCH) {
                if (!$this->_headerSignKey && !$this->_headerToken) {
                    echo "<h1>$this->_default_error_message . $event_logger_id</h1>";
                } else {
                    $this->_result['result'] = H\GTResult::to($this->_default_error_message . $event_logger_id, true, $this->_customConfig['errorCodeResponse']);
                    $json = json_encode($this->_result);
                    echo $json;
                    die;
                }
            }
        }
    }

    private function redirectIfDevMode($event_logger_id) {
        if ($this->_customConfig['developmentMode']) {
            if (!$this->_headerSignKey && !$this->_headerToken) {
                $redirect ="http://" . ($_SERVER['SERVER_NAME']) . "/gtevent/show/event_id/" . $event_logger_id;
                echo "<a href='" . $redirect . "'>$redirect</a>";
            } else {
                $html = 'Event Id:' . $event_logger_id . '<br>';
                $html .= H\EventType::getName($this->_f_event_type) . '<br>';
                $html .= $this->_f_event_code . '<br>';
                $html .= $this->_f_event_file . ':' . $this->_f_line . '<br>';
                $html .= $this->_f_message . '<br>';
                $html .= $this->_f_stack_trace . '<br>';
                $html .= 'User Id:' . $this->_f_user_id . '<br>';
                $html .= 'Device Id:' . $this->_f_device_id . '<br>';

                $this->_result['result'] = H\GTResult::to($this->_default_error_message . $event_logger_id, true, $this->_customConfig['errorCodeResponse']);
                $this->_result['result'] = array(
                    'stack_trace' => $html,
                    'exception_message' => $this->_f_message,
                    'datetime' => $this->_f_date_time,
                    'message' => $this->_default_error_message . "$event_logger_id, $this->_f_message",
                    'code' => 3
                );
                $json = json_encode($this->_result);
                echo $json;
            }
        } else {
            $redirect ="http://" . ($_SERVER['SERVER_NAME']) . "/gtevent/error";
            if ($this->_f_event_type == EventType::ERROR_PHP ||
                $this->_f_event_type == EventType::EXCEPTION_DISPATCH ||
                $this->_f_event_type == EventType::EXCEPTION_RENDER ||
                $this->_f_event_type == EventType::EXCEPTION_PHP) {
                if (!$this->_headerSignKey && !$this->_headerToken) {
                    echo "<meta http-equiv='refresh' content='0;url=$redirect'>";
                } else {
                    $this->_result['result'] = H\GTResult::to($this->_default_error_message . $event_logger_id, true, $this->_customConfig['errorCodeResponse']);
                    $json = json_encode($this->_result);
                    echo $json;
                }
                die;
            }
            if ($this->_f_event_type == EventType::ROUTER_NOT_MATCH) {
                if (!$this->_headerSignKey && !$this->_headerToken) {
                    echo "<meta http-equiv='refresh' content='0;url=$redirect'>";
                } else {
                    $this->_result['result'] = H\GTResult::to($this->_default_error_message . $event_logger_id, true, $this->_customConfig['errorCodeResponse']);
                    $json = json_encode($this->_result);
                    echo $json;
                }
            }
        }
    }

    /**
     * @param array $trace
     * @param string $title
     * @return String
     * @throws \Exception
     */
    private function stackTraceProcessing($trace, $title = "") {
        // Handle array
        for($i = 0; $i < count($trace); $i++) {
            if (!isset($trace[$i]['file'])) {$trace[$i]['file'] = 'unknown file';}
            if (!isset($trace[$i]['line'])) {$trace[$i]['line'] = 'unknown line';}
            if (!isset($trace[$i]['function'])) {$trace[$i]['function'] = 'unknown function';}

            if (isset($trace[$i]['args']['0']) &&
                !is_array($trace[$i]['args']['0']) &&
                !is_object($trace[$i]['args']['0'])) {

                $trace[$i]['params'] = '(' . $trace[$i]['args']['0'] . ')';
            } else {
                $trace[$i]['params'] = '()';
            }
        }
        // Convert processed array to html string
        $customErrorView = new PhpRenderer();
        $resolver = new AggregateResolver();
        $customErrorView->setResolver($resolver);
        $resolver->attach(new TemplateMapResolver(array(
            'error_template' => dirname(dirname(dirname(__DIR__))) . '/view/gt-error-tracker/event/event_template.phtml'
        )));

        $model = new ViewModel(array(
            'errorStackTrace' => $trace,
            'message' => $title
        ));
        $model->setTemplate('error_template');

        return $customErrorView->render($model);
    }

    public function getDateTimeFormat($format = DATE_RFC822) {
        $dt = H\Env::getDateTime($this->get_date_time());
        return $dt->format($format);
    }

}
?>