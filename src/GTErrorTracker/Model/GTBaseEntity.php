<?php

namespace GTErrorTracker\Model;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of BaseEntity
 *
 * @author maxim
 */
class GTBaseEntity {

    private $_serviceLocator;

    function __construct($_serviceLocator = null) {
        $this->_serviceLocator = $_serviceLocator;
    }

    public function __set($name, $value) {
        $method = "set_" . $name;
        if (($name == "mapper") || !method_exists($this, $method)) {
            throw new \Exception("Invalid property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get_" . $name;
        if (($name == "mapper") || !method_exists($this, $method)) {
            throw new \Exception("Invalid property");
        }
        return $this->$method();
    }

    public function exchangeArray($options) {
        $options = $options ? $options : array();
        $methods = get_class_methods($this);
        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                $method = "set_" . lcfirst($key);
                if (in_array($method, $methods)) {
                    $this->$method($value);
                }
            }
        } else {
            $reflecionObject = new \ReflectionObject($this);
            foreach ($reflecionObject->getProperties(\ReflectionProperty::IS_PRIVATE) as $propiedad) {
                $prefix = substr($propiedad->getName(), 0, 3);
                if ($prefix == "_f_") {
                    $field = substr($propiedad->getName(), 3);
                    $method = "set_" . lcfirst($field);
                    $reflecionObject->getMethod($method)->invoke($this, null);
                }
            }
        }
        return $this;
    }

    public function getArrayCopy() {
        $object = array();
        $reflecionObject = new \ReflectionObject($this);
        foreach ($reflecionObject->getProperties(\ReflectionProperty::IS_PRIVATE) as $propiedad) {
            $prefix = substr($propiedad->getName(), 0, 3);
            if ($prefix == "_f_") {
                $field = substr($propiedad->getName(), 3);
                $method = "get_" . lcfirst($field);
                $object[$field] = $reflecionObject->getMethod($method)->invoke($this);
            }
        }
        return $object;
    }

    public function show() {
        $result = "";
        $args = func_get_args();
        if (count($args) > 0) {
            $method = "get_" . $args[0];
            if (!method_exists($this, $method)) {
                $method = "get" . $args[0];
            }
            if (method_exists($this, $method)) {
                if (count($args) > 1) {
                    $result = $this->$method($args[1]);
                } else {
                    $result = $this->$method();
                }
            }
        }
        echo($result);
    }

    public function toArray() {
        return $this->getArrayCopy();
    }

    public function setServiceLocator($serviceLocator) {
        if ($serviceLocator != null && $serviceLocator instanceof ServiceLocatorInterface) {
            $this->_serviceLocator = $serviceLocator;
        }
    }

    public function getServiceLocator() {
        return $this->_serviceLocator;
    }

    public function gateway($gatewayName) {
        assert(is_string($gatewayName) && strlen(trim($gatewayName)) > 0);
        return $this->_serviceLocator->get("GTErrorTracker\\Model\\Gateway\\" . $gatewayName);
    }
    public function GlobalGateway($gatewayName) {
        assert(is_string($gatewayName) && strlen(trim($gatewayName)) > 0);
        return $this->_serviceLocator->get($gatewayName);
    }



}

?>
