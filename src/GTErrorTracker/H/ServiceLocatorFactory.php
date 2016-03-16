<?php

namespace GTErrorTracker\H;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ServiceLocatorFactory implements ServiceLocatorAwareInterface {
    /**
     * @var ServiceManager
     */
    private $serviceManager = null;

    private static $singleton = null;

    /**
     * Disable constructor
     */
    private function __construct() { }

    /**
     * @return ServiceLocatorFactory
     */
    public static function getInstance() {
        if(null === self::$singleton) {
            self::$singleton = new ServiceLocatorFactory();
        }
        return self::$singleton;
    }

    /**
     * @param ServiceManager $sm
     */
    public function setInstance(ServiceManager $sm) {
        $this->serviceManager = $sm;
    }

    /**
     * Set service locator
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Get service locator
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceManager;
    }
}