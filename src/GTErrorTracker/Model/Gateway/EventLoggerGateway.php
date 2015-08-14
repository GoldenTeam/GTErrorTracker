<?php

namespace GTErrorTracker\Model\Gateway;

use GTErrorTracker\Model\EventLogger;
use GTErrorTracker\H;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\ServiceManager\ServiceManager;

class EventLoggerGateway extends GTBaseTableGateway implements AdapterInterface {

    private $_count = -1;
    private $_options = array();

    function __construct(Adapter $dbAdapter, ServiceManager $sm) {
        $config = $sm->get('config');
        $this->table = $config["GTErrorTracker"]["GTTableName"];
        parent::__construct($dbAdapter);
    }

    public function getEntity() {
        return new EventLogger();
    }


    public function deteteByParams($params) {

        $time = H\Env::getDateTime()->getTimestamp();
        echo "$time\n\n";
        echo strtotime($time);

        if ($params['GTErrorTypesDeleteFromDb']['EXCEPTION_DISPATCH']) {
            $result['EXCEPTION_DISPATCH'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 1 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['EXCEPTION_DISPATCH']);
                $delete->where($where);
            });
        } else {
            $result['EXCEPTION_DISPATCH'] = 0;
        }


        if ($params['GTErrorTypesDeleteFromDb']['EXCEPTION_RENDER']) {
            $result['EXCEPTION_RENDER'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 2 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['EXCEPTION_RENDER']);
                $delete->where($where);
            });
        } else {
            $result['EXCEPTION_RENDER'] = 0;
        }

        if ($params['GTErrorTypesDeleteFromDb']['ERROR_PHP']) {
            $result['ERROR_PHP'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 3 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['ERROR_PHP']);
                $delete->where($where);
            });
        } else {
            $result['ERROR_PHP'] = 0;
        }


        if ($params['GTErrorTypesDeleteFromDb']['EXCEPTION_PHP']) {
            $result['EXCEPTION_PHP'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 4 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['EXCEPTION_PHP']);
                $delete->where($where);
            });
        } else {
            $result['EXCEPTION_PHP'] = 0;
        }

        if ($params['GTErrorTypesDeleteFromDb']['WARNING_PHP']) {
            $result['WARNING_PHP'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 5 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['WARNING_PHP']);
                $delete->where($where);
            });
        } else {
            $result['WARNING_PHP'] = 0;
        }


        if ($params['GTErrorTypesDeleteFromDb']['NOTICE_PHP']) {
            $result['NOTICE_PHP'] = $this->delete(function (Delete $delete) use ($params, $time) {
                $where = new Where();
                $where->expression("event_type = 6 AND date_time < ?", $time - $params['GTErrorTypesDeleteFromDbByTime']['NOTICE_PHP']);
                $delete->where($where);
            });
        } else {
            $result['NOTICE_PHP'] = 0;
        }


        return $result;
    }




    public function findByEventLoggerId($event_logger_id) {
        $this->result("Can't find event by id");
        $event_logger_id = intval($event_logger_id);
        $item = $this->select(array('event_logger_id' => $event_logger_id))->current();
        if ($item != null) {
            $this->result("", false);
        }
        return $item;
    }

    public function remove(EventLogger $event_logger) {
        $this->result("Can't delete event by id");
        $event_logger_id  = intval($event_logger->get_event_logger_id());

        $affected = $this->delete(array('event_logger_id' => $event_logger_id));
        if ($affected >= 0) {
            $this->result("Event was deleted successfully.", false);
        }
        return $affected;
    }

    public function save(EventLogger $eventLogger) {
        try {
            $this->result("Can't save event into the database");
            $data = $eventLogger->getArrayCopy();
            unset($data["event_logger_id"]);
            $event_logger_id = $eventLogger->get_event_logger_id();
            if ($event_logger_id == null || $event_logger_id < 0) {
                $affected = $this->insert($data);
                if ($affected > 0) {
                    $eventLogger->set_event_logger_id($this->getLastInsertValue());
                    $this->result("Event was created successfully.", false);
                }
            } else {
                $affected = $this->update($data, array('event_logger_id' => $event_logger_id));
                $this->result("Event was updated successfully.", false);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @return HydratingResultSet
     */
    public function findAll() {
        $this->result("Can't find all events");
        $resultSet = $this->select();
        if ($resultSet->count() >= 0) {
            $this->result("", false);
        }
        return $resultSet;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an user objects
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        if ($this->_count <= 0) {
            if (isset($this->_options["eventData"]) && $this->_options["eventData"] != '') {

                $where = new \Zend\Db\Sql\Where();
                $where->expression("CONCAT_WS(' ', event_file, event_code, event_hash) LIKE ?", "%" . $this->_options["eventData"] . "%");
                $sql = new Sql($this->adapter);
                $select = $sql->select();

                $select
                    ->from($this->table)
                    ->where($where)
                    ->columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(*)')));

                $sqlTxt = $sql->getSqlStringForSqlObject($select);
                $resultSet = $this->adapter->query($sqlTxt, Adapter::QUERY_MODE_EXECUTE);
                foreach ($resultSet as $row) {
                    $this->_count = intval($row->count);
                    break;
                }


            } else {
                $where = new \Zend\Db\Sql\Where();

                $sql = new Sql($this->adapter);
                $select = $sql->select();

                $select
                    ->from($this->table)
                    ->where($where)
                    ->columns(array('count' => new \Zend\Db\Sql\Expression('COUNT(*)')));

                $sqlTxt = $sql->getSqlStringForSqlObject($select);
                $resultSet = $this->adapter->query($sqlTxt, Adapter::QUERY_MODE_EXECUTE);
                foreach ($resultSet as $row) {
                    $this->_count = intval($row->count);
                    break;
                }
            }
        }
        return $this->_count;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $limit) {
        $options = $this->_options;
        $items = $this->select(function(Select $select) use ($offset, $limit, $options) {
            $where = new Where();
            $select->where($where);
            $select->offset($offset)->limit($limit);
            $select->order('date_time DESC');
        if (isset($this->_options["eventData"]) && $this->_options["eventData"] != '') {
            $where = new Where();
            $where->expression("CONCAT_WS(' ', event_file, event_code, event_hash, line, message) LIKE ?", "%" . $this->_options["eventData"] . "%");
            $select->where($where);
        }
        });
        if ($items->count() > 0) {
            $this->result("", false);
        }
        return $items;
    }

    public function findByEventId($event_logger_id) {
        $this->result("Can't find event by event id");
        $event_logger_id = intval($event_logger_id);
        $customEvent = $this->select(array('event_logger_id' => $event_logger_id))->current();
        if ($customEvent != null) {
            $this->result("", false);
        }
        return $customEvent;
    }

    public function setOptions($filter)
    {
        $this->_options = $filter;
    }
}
?>