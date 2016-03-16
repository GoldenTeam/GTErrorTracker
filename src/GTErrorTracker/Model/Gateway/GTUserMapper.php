<?php

namespace GTErrorTracker\Model\Gateway;

use GTErrorTracker\Model\GTUserInterface;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class GTUserMapper extends AbstractTableGateway implements ServiceLocatorAwareInterface  {

    protected $pkName  = '';
    private $sm = null;

    protected $_hydrator;
    protected $_entity;

    public function setServiceLocator(ServiceLocatorInterface $sm) {
        $config = $sm->get('config');
        $this->table = $config["GTErrorTracker"]["GTUserTableName"];
        $this->pkName = $config["GTErrorTracker"]["GTTablePrimaryKey"];

        $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        $this->resultSetPrototype = new HydratingResultSet($this->_hydrator);
        $this->resultSetPrototype->setObjectPrototype($this->_entity);
        $this->initialize();
    }

    public function getServiceLocator() {
        return $this->sm;
    }

    public function setEntityPrototype(GTUserInterface $userEntity) {
        $this->_entity = $userEntity;
    }

    public function setHydrator(HydratorInterface $hydrator) {
        $this->_hydrator = $hydrator;
    }

    public function findById($user_id) {
        return $this->select(array($this->pkName => $user_id))->current();
    }

}