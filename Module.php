<?php

namespace GTErrorTracker;

require_once 'GTErrorTracker.php';

use GTErrorTracker\H;
use GTErrorTracker\H\EventType;
use GTErrorTracker\H\ServiceLocatorFactory;
use GTErrorTracker\Model;
use GTErrorTracker\Model\EventLogger;
use GTErrorTracker\Model\Gateway\EventLoggerGateway;

use Zend\Mvc\MvcEvent;

class Module {
    public function onBootstrap(MvcEvent $e) {

        $serviceManager = $e->getApplication()->getServiceManager();

        $application = $e->getApplication();
        $eventManager = $application->getEventManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function ($event) use ($serviceManager) {
            $exception = $event->getResult()->exception;
            if ($exception instanceof \Exception) {
                $errorInfo = new EventLogger($serviceManager);
                $errorInfo->set_event_type(EventType::EXCEPTION_DISPATCH);
                $errorInfo->handle($exception);
                die;
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
            )
        );
    }
}
