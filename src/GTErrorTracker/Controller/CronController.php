<?php

namespace GTErrorTracker\Controller;

use GTErrorTracker\H;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
class CronController extends AbstractActionController {

    public function deleteAction() {
        echo "Welcome To Cron Delete Action!\n";

        $request = $this->getRequest();
        $config = $this->getServiceLocator()->get('config');
        $customConfig = $config["GTErrorTracker"];
        $params = $customConfig['GTCronSettings'];

        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        echo "1 is true (entries will be found and deleted from database)\n";
        print_r($params);
        $eventLogger["Number of Deleted Entries From Data Base"] = $this->GTGateway("EventLoggerGateway")->deleteByParams($params);
        print_r($eventLogger);
    }

}
?>