<?php

namespace GTErrorTracker\Model;

use GTErrorTracker\H;
use GTErrorTracker\H\EventType;
use GTErrorTracker\H\ServiceLocatorFactory;

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

    protected $serviceManager;

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

    function __construct($_serviceLocator = null) {
        parent::__construct($_serviceLocator);

        $this->_f_date_time = H\Env::getDateTime()->getTimestamp();

        $session = new Container('mobile');
        $this->_f_device_id = isset($session->device_id)?$session->device_id:null;

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

    public function handle()
    {
        $args = func_get_arg(0);
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
            }
            $this->_f_event_code = $type;
            $this->_f_message = "Backtrace from $this->_f_event_code $errstr at $this->_f_event_file $this->_f_line ";
            $this->_f_stack_trace = $this->stackTraceProcessing($trace, $this->_f_message);
        }

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

            if($this->_f_date_time - $session->errorTime < 2) {

                $this->echoIfDevMode($session->lastEventId);

            } else {

                $session->errorTime = $this->_f_date_time;         // save time when error has been occurred
                $this->redirectIfDevMode($session->lastEventId);
            }

        } else {
            $this->_f_event_hash = $event_hash;

            $config = ServiceLocatorFactory::getInstance()->getServiceLocator()->get('config');
            $customConfig = $config["GTErrorTracker"];

            if ($customConfig["GTErrorTypesSaveToDb"][H\EventType::getName($this->_f_event_type)]) {
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
        if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'development') {

            echo 'Event Id:' . $event_logger_id . '<br>';
            //echo 'Date:' . $this->getDateTimeFormat($this->_f_date_time) . '<br>';
            echo H\EventType::getName($this->_f_event_type) . '<br>';
            echo $this->_f_event_code . '<br>';
            echo $this->_f_event_file . ':' . $this->_f_line . '<br>';
            echo $this->_f_message . '<br>';
            echo $this->_f_stack_trace . '<br>';
            echo 'User Id:' . $this->_f_user_id . '<br>';
            echo 'Device Id:' . $this->_f_device_id . '<br>';

        } else {
            if ($this->_f_event_type == EventType::ERROR_PHP ||
                $this->_f_event_type == EventType::EXCEPTION_DISPATCH ||
                $this->_f_event_type == EventType::EXCEPTION_RENDER ||
                $this->_f_event_type == EventType::EXCEPTION_PHP) {

                echo "<h1>Some Unexpected Error occurred. Please contact to administrator.</h1>";
                die;
            }
        }
    }

    private function redirectIfDevMode($event_logger_id) {
        if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'development') {
            $redirect ="http://" . ($_SERVER['SERVER_NAME']) .
                "/gtevent/show/event_id/" . $event_logger_id;
            echo "<meta http-equiv='refresh' content='0;url=$redirect'>";
        } else {

            if ($this->_f_event_type == EventType::ERROR_PHP ||
                $this->_f_event_type == EventType::EXCEPTION_DISPATCH ||
                $this->_f_event_type == EventType::EXCEPTION_RENDER ||
                $this->_f_event_type == EventType::EXCEPTION_PHP) {
                $redirect ="http://" . ($_SERVER['SERVER_NAME']) .
                    "/gtevent/error";
                echo "<meta http-equiv='refresh' content='0;url=$redirect'>";
                die;
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