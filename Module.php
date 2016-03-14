<?php

namespace GTErrorTracker;

require_once 'GTErrorTracker.php';

use GTErrorTracker\H;
use GTErrorTracker\H\EventType;
use GTErrorTracker\H\ServiceLocatorFactory;
use GTErrorTracker\Model;
use GTErrorTracker\Model\EventLogger;
use GTErrorTracker\Model\Gateway\EventLoggerGateway;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class Module {
    public function onBootstrap(MvcEvent $e) {

        $serviceManager = $e->getApplication()->getServiceManager();

        $application = $e->getApplication();
        $eventManager = $application->getEventManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function ($event) use ($serviceManager) {
            $serviceManager = ServiceLocatorFactory::getInstance()->getServiceLocator();
            $action = $event->getRouteMatch()->getParam('action');
            $action = $action . "Action";
            if (!method_exists($event->getTarget(), $action)) {
                $url = $event->getRequest()->getUri()->getPath();
                $errorInfo = new EventLogger($serviceManager);
                $errorInfo->set_event_type(EventType::ROUTER_NOT_MATCH);
                $trace = debug_backtrace();
                array_shift($trace);
                $errorMessage = 'The requested controller ' . $event->getRouteMatch()->getParam('controller') . ' was unable to dispatch the request';
                $errorInfo->handle(Application::ERROR_CONTROLLER_CANNOT_DISPATCH, $errorMessage, null, null, $trace, null, $url);
            }
        });
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function ($event) use ($serviceManager) {
            $exception = $event->getResult()->exception;
            if ($exception instanceof \Exception) {
                $errorInfo = new EventLogger($serviceManager);
                $errorInfo->set_event_type(EventType::EXCEPTION_DISPATCH);
                $errorInfo->handle($exception);
                die;
            }

            $error = $event->getError();
            $url = $event->getRequest()->getUri()->getPath();

            $errorMessage = "";
            if ($error == Application::ERROR_CONTROLLER_NOT_FOUND) {
                $errorMessage = 'The requested controller ' . $event->getRouteMatch()->getParam('controller') . ' could not be mapped to an existing controller class';
            }
            if ($error == Application::ERROR_CONTROLLER_INVALID) {
                $errorMessage = 'The requested controller ' . $event->getRouteMatch()->getParam('controller') . ' is not dispatchable';
            }
            if ($error == Application::ERROR_ROUTER_NO_MATCH) {
                $errorMessage = 'The requested URL could not be matched by routing';
            }
            if ($error == Application::ERROR_CONTROLLER_NOT_FOUND ||
                $error == Application::ERROR_CONTROLLER_INVALID ||
                $error == Application::ERROR_ROUTER_NO_MATCH
            ) {
                $trace = debug_backtrace();
                array_shift($trace);
                $errorInfo = new EventLogger($serviceManager);
                $errorInfo->set_event_type(EventType::ROUTER_NOT_MATCH);
                $errorInfo->handle($error, $errorMessage, null, null, $trace, null, $url);
            }
        });

        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function ($event) use ($serviceManager) {
            $exception = $event->getResult()->exception;
            if ($exception instanceof \Exception) {
                $errorInfo = new EventLogger($serviceManager);
                $errorInfo->set_event_type(EventType::EXCEPTION_RENDER);
                $errorInfo->handle($exception);
                die;
            }
        });

        $factory = ServiceLocatorFactory::getInstance();
        $factory->setServiceLocator($serviceManager);
    }

    public function getAutoloaderConfig() {

        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig() {

        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'GTErrorTracker\Model\Gateway\EventLoggerGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new EventLoggerGateway($dbAdapter, $sm);
                },
                'gt_user_entity' => function () {
                    return null;
                },
            )
        );
    }
}
