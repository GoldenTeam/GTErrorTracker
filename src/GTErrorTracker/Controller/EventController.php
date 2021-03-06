<?php
namespace GTErrorTracker\Controller;

use GTErrorTracker\Form\GTEventSearchForm;
use GTErrorTracker\H;
use GTErrorTracker\H\Env;
use GTErrorTracker\Model\EventLogger;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Session\Container;

class EventController extends AbstractActionController {

    private function getEvents($eventLoggerGateway) {
        $session = new Container('user');
        $pageNum = $session->page;
        $itemsPerPage = $session->itemsPerPage ? $session->itemsPerPage : Env::EVENT_PAGER;
        $filter = $session->eventData;
        $this->GTGateway('EventLoggerGateway')->setOptions($filter);
        $pager = new Paginator($eventLoggerGateway);
        $pager->setCurrentPageNumber($pageNum)->setItemCountPerPage($itemsPerPage);
        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $sm = $this->getServiceLocator();
        $formSearchEvent = new GTEventSearchForm($sm);
        $searchHtmlForm = $partial("gt-error-tracker/event/search.phtml",
            array("formSearchEvent" => $formSearchEvent, "searchValue" => $filter['eventData']));
        $result = array(
            "pagerHtml" => $pager->count() > 0 ?
                $partial("gt-error-tracker/event/event_list.phtml",
                    array("pager" => $pager, "count" => $eventLoggerGateway->count(), "formSearchHtml" => $searchHtmlForm)) :
                $partial("gt-error-tracker/empty_list.phtml",
                    array('message' => 'No events found so far', "formSearchHtml" => $searchHtmlForm)),
            "page" => $pageNum,
        );
        return $result;
    }

    public function deleteAction() {
        $event_logger_id = $this->GTParam('event_logger_id', 0);
        $EG = $this->GTGateway("EventLoggerGateway");
        $eventLogger = $EG->findByEventLoggerId($event_logger_id);
        $result = H\GTResult::to();
        if ($eventLogger instanceof EventLogger) {
            $eventLogger->delete();
            $result = H\GTResult::to();
            if (!H\GTResult::isError()) {
                $result = $this->getEvents($EG);
            }
        }
        return $this->GTResult($result);
    }

    public function indexAction() {
        $session = new Container('user');
        $config = $this->getServiceLocator()->get('config');
        $customConfig = $config["GTErrorTracker"];
        $pageNum = $this->GTParam('page', 0);
        $session->page = $pageNum;
        $session->itemsPerPage = $customConfig['itemsPerPage'] ? $customConfig['itemsPerPage'] : Env::EVENT_PAGER;
        $eventData = $this->params()->fromPost('GTEventData', '###');
        if ($eventData != "###") {
            $filter['eventData'] = $eventData;
            $session->eventData = $filter;
        }
        $EG = $this->GTGateway("EventLoggerGateway");
        $result = $this->getEvents($EG);
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", $customConfig['LoadCss']['indexAction']);
            $this->GTHead("js",  $customConfig['LoadJs']['indexAction']);
            $this->GTHead("init", array("eventList.init(".$pageNum.");"));
        }
        return $this->GTResult($result);
    }

    public function errorAction() {
        $config = $this->getServiceLocator()->get('config');
        $customConfig = $config["GTErrorTracker"];
        $result = array("message" => "Some Error Occurred. Please Contact to the Administrator");
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", $customConfig['LoadCss']['indexAction']);
            $this->GTHead("js",  $customConfig['LoadJs']['indexAction']);
        }
        return $this->GTResult($result);
    }

    public function showAction() {
        $session = new Container('user');
        $pageNum =  $session->page;

        $config = $this->getServiceLocator()->get('config');
        $customConfig = $config["GTErrorTracker"];

        $event_logger_id = $this->GTParam('event_logger_id', 0);
        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $customEvent = $this->GTGateway("EventLoggerGateway")->findByEventId($event_logger_id);
        $result = H\GTResult::to();
        if ($customEvent instanceof EventLogger) {
            $xdebug_message = $customEvent->get_xdebug_message() ?
                $partial("gt-error-tracker/event/xdebug_message.phtml", array("item" => $customEvent)) : "";

            $variables_dump = $customEvent->get_variables_dump() ?
                $partial("gt-error-tracker/event/variables_dump.phtml", array("item" => $customEvent)) : "";

            $trace_dump = $customEvent->get_trace_dump() ?
                $partial("gt-error-tracker/event/trace_dump.phtml", array("item" => $customEvent)) : "";

            $result = array(
                "html" =>
                    $partial("gt-error-tracker/event/event_item.phtml",
                        array("item" => $customEvent,
                            "pageNum" => $pageNum,
                            "xdebug_message" =>  $xdebug_message,
                            "variables_dump" => $variables_dump,
                            "trace_dump" => $trace_dump
                        )
                    )
                );
        } else {
            $result = array(
                "html" => $partial("gt-error-tracker/empty_list.phtml",
                    array('message' => $result['message'])));
        }
        if (!$this->getRequest()->isPost()) {
            $this->GTHead("css", $customConfig['LoadCss']['showAction']);
            $this->GTHead("js",  $customConfig['LoadJs']['showAction']);
        }
        return $this->GTResult($result);
    }
}
?>