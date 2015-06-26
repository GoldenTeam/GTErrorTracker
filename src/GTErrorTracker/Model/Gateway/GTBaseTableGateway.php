<?php

namespace GTErrorTracker\Model\Gateway;

use GTErrorTracker\H;
use GTErrorTracker\Model\GTBaseEntity;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of BaseTableGateway
 *
 * @author kalin-mv
 */
abstract class GTBaseTableGateway extends AbstractTableGateway implements ServiceLocatorAwareInterface {

    private $_entity;
    private $_serviceManager;

    /**
     * @return GTBaseEntity;
     */
    abstract function getEntity();

    public function __construct(Adapter $dbAdapter) {
        $this->_entity = $this->getEntity();
        $this->_entity->setServiceLocator($this->getServiceLocator());
        $this->adapter = $dbAdapter;
        $this->resultSetPrototype = new HydratingResultSet(new GTEntityHydrator());
        $this->resultSetPrototype->setObjectPrototype($this->_entity);
        $this->initialize();
    }

    public function result($message = null, $error = true, $code = 0) {
        return H\GTResult::to($message, $error, $code);
    }

    public function isError() {
        return H\GTResult::isError();
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->_serviceManager = $serviceLocator;
        if ($this->_entity instanceof GTBaseEntity) {
            $this->_entity->setServiceLocator($serviceLocator);
        }
    }

    public function getServiceLocator() {
        return $this->_serviceManager;
    }

    public function gateway($gatewayName) {
        assert(is_string($gatewayName) && strlen(trim($gatewayName)) > 0);
        return $this->_serviceManager->get("GTErrorTracker\\Model\\Gateway\\" . $gatewayName);
    }

    public function query($sql, $params = array()) {
        $items = $this->getAdapter()->query($sql, $params);
        $items->setArrayObjectPrototype($this->getEntity());
        if ($items->count() > 0) {
            $this->result("", false);
        }
        return $items;
    }

    public function beginTransaction() {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }

    public function commit() {
        $this->adapter->getDriver()->getConnection()->commit();
    }

    public function rollback() {
        $this->adapter->getDriver()->getConnection()->rollback();
    }

}
?>
