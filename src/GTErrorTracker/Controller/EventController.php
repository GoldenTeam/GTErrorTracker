<?php
/**
 * Created by PhpStorm.
 * User: melnik-da
 * Date: 6/17/15
 * Time: 2:48 PM
 */
namespace GTErrorTracker\Controller;

use GTErrorTracker\Form\GTEventSearchForm;
use GTErrorTracker\H;
use GTErrorTracker\H\Env;
use GTErrorTracker\Model\EventLogger;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Session\Container;

class EventController extends AbstractActionController {

    public function deleteAction() {
        $session = new Container('user');
        $pageNum = $this->GTParam('page', 0);
        $event_logger_id = $this->GTParam('event_logger_id', 0);
        $eventLogger = $this->GTGateway("EventLoggerGateway")->findByEventLoggerId($event_logger_id);
        $result = H\GTResult::to();
        if ($eventLogger instanceof EventLogger) {
            $eventLogger->delete();
            $result = H\GTResult::to();
            if (!H\GTResult::isError()) {
                $eventData = $this->params()->fromPost('GTEventData', '###');
                if ($eventData!="###" && $eventData!="") {
                    $filter['eventData'] = $eventData;
                    $session->eventData = $filter;
                    $this->GTGateway('EventLoggerGateway')->setOptions($session->eventData);
                }
                $EG = $this->GTGateway("EventLoggerGateway");
                $pager = new Paginator($EG);
                $pager->setCurrentPageNumber($pageNum)->setItemCountPerPage(Env::EVENT_PAGER);
                $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
                $sm = $this->getServiceLocator();
                $formSearchEvent = new GTEventSearchForm($sm);
                $searchHtmlForm =  $partial("gt-error-tracker/event/search.phtml", array("formSearchEvent" => $formSearchEvent));
                $result = array(
                    "pagerHtml" => $pager->count() > 0 ?
                        $partial("gt-error-tracker/event/event_list.phtml", array("pager" => $pager, "count" => $EG->count(), "formSearchHtml" => $searchHtmlForm)) :
                        $partial("gt-error-tracker/emtpy_list.phtml", array('message' => 'No events found so far', "formSearchHtml" => $searchHtmlForm)),
                    "page" => $pageNum,

                );
            }
        }
        return $this->GTResult($result);
    }

    public function indexAction() {
        $session = new Container('user');

        $config = $this->getServiceLocator()->get('config');
        $customConfig = $config["GTErrorTracker"];
        $sm = $this->getServiceLocator();
        $formSearchEvent = new GTEventSearchForm($sm);
        $eventData = $this->params()->fromPost('GTEventData', '###');
        if ($eventData!="###" && $eventData!="") {
            $filter['eventData'] = $eventData;
            $session->eventData = $filter;
            $this->GTGateway('EventLoggerGateway')->setOptions($session->eventData);
        }

        $pageNum = $this->GTParam('page', 0);
        $session->page = $pageNum;

        $EG = $this->GTGateway("EventLoggerGateway");
        $pager = new Paginator($EG);
        $pager->setCurrentPageNumber($pageNum)->setItemCountPerPage(Env::EVENT_PAGER);
        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $searchHtmlForm = $partial("gt-error-tracker/event/search.phtml", array("formSearchEvent" => $formSearchEvent));
        $result = array(
            "pagerHtml" => $pager->count() > 0 ?
                $partial("gt-error-tracker/event/event_list.phtml", array("pager" => $pager, "count" => $EG->count(), "formSearchHtml" => $searchHtmlForm)) :
                $partial("gt-error-tracker/emtpy_list.phtml", array('message' => 'No events found so far', "formSearchHtml" => $searchHtmlForm)),
            "page" => $pageNum,

        );
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
            if ($customEvent->get_xdebug_message()!=null) {
                $xdebug_message = $partial("gt-error-tracker/event/xdebug_message.phtml",
                    array("item" => $customEvent)
                );
            } else {
                $xdebug_message = "";

            }
            if ($customEvent->get_variables_dump()!=null) {
                $variables_dump = $partial("gt-error-tracker/event/variables_dump.phtml",
                    array("item" => $customEvent)
                );
            } else {
                $variables_dump = "";
            }

            if ($customEvent->get_trace_dump()!=null) {
                $trace_dump = $partial("gt-error-tracker/event/trace_dump.phtml",
                    array("item" => $customEvent)
                );
            } else {
                $trace_dump = "";
            }

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
                "html" => $partial("gt-error-tracker/emtpy_list.phtml",
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