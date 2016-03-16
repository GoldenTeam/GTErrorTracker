<?php

namespace GTErrorTracker\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class GTGateway extends AbstractPlugin {

    public function __invoke($gatewayName) {
        assert(is_string($gatewayName) && strlen(trim($gatewayName)) > 0);
        $controller = $this->getController();
        $sm = $controller->getServiceLocator();
        return $sm->get("GTErrorTracker\\Model\\Gateway\\".$gatewayName);
    }

}