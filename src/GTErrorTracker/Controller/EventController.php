<?php
/**
 * Created by PhpStorm.
 * User: melnik-da
 * Date: 6/17/15
 * Time: 2:48 PM
 */
namespace GTErrorTracker\Controller;

use GTErrorTracker\H;
use GTErrorTracker\H\Env;
use GTErrorTracker\Model\EventLogger;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;

class EventController extends AbstractActionController {

    public function deleteAction() {
        $pageNum = $this->GTParam('page', 0);
        $event_logger_id = $this->GTParam('event_logger_id', 0);
        $eventLogger = $this->GTGateway("EventLoggerGateway")->findByEventLoggerId($event_logger_id);
        $result = H\GTResult::to();
        if ($eventLogger instanceof EventLogger) {
            $eventLogger->delete();
            $result = H\GTResult::to();
            if (!H\GTResult::isError()) {

                $EG = $this->GTGateway("EventLoggerGateway");
                $pager = new Paginator($EG);
                $pager->setCurrentPageNumber($pageNum)->setItemCountPerPage(Env::EVENT_PAGER);
                $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
                $result = array(
                    "pagerHtml" => $pager->count() > 0 ?
                        $partial("gt-error-tracker/event/event_list.phtml", array("pager" => $pager, "count" => $EG->count())) :
                        $partial("gt-error-tracker/emtpy_list.phtml", array('message' => 'No events found so far')),
                    "page" => $pageNum,

                );
            }
        }
        return $this->GTResult($result);
    }

    public function indexAction() {
        $pageNum = $this->GTParam('page', 0);
        $EG = $this->GTGateway("EventLoggerGateway");
        $pager = new Paginator($EG);
        $pager->setCurrentPageNumber($pageNum)->setItemCountPerPage(Env::EVENT_PAGER);
        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $result = array(
            "pagerHtml" => $pager->count() > 0 ?
                $partial("gt-error-tracker/event/event_list.phtml", array("pager" => $pager, "count" => $EG->count())) :
                $partial("gt-error-tracker/emtpy_list.phtml", array('message' => 'No events found so far')),
            "page" => $pageNum,

        );
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", array(
                "GTErrorTracker/event-index",));
            $this->GTHead("js", array(
                "typeahead", "GTErrorTracker/event-index", "GTErrorTracker/gtmain"));
            $this->GTHead("init", array("eventList.init(".$pageNum.");"));
        }
        return $this->GTResult($result);
    }

    public function errorAction() {
        $result = array("message" => "Some Error Occurred. Please Contact to the Administrator");
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", array(
                "GTErrorTracker/event-index",));
            $this->GTHead("js", array(
                "typeahead", "GTErrorTracker/event-index", "GTErrorTracker/gtmain"));
        }
        return $this->GTResult($result);
    }

    public function showAction() {
        $event_logger_id = $this->GTParam('event_logger_id', 0);
        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $customEvent = $this->GTGateway("EventLoggerGateway")->findByEventId($event_logger_id);
        $result = H\GTResult::to();
        if ($customEvent instanceof EventLogger) {
            $result = array(
                "html" => $partial("gt-error-tracker/event/event_item.phtml",
                    array("item" => $customEvent)));
        } else {
            $result = array(
                "html" => $partial("gt-error-tracker/emtpy_list.phtml",
                    array('message' => $result['message'])));
            }
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", array(
                "GTErrorTracker/event-index",));
            $this->GTHead("js", array(
                "typeahead", "GTErrorTracker/event-index", "GTErrorTracker/gtmain"));
        }
        return $this->GTResult($result);
    }
}

?>