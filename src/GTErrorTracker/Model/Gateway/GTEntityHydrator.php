<?php

namespace GTErrorTracker\Model\Gateway;

use Zend\Server\Reflection;
use Zend\Stdlib\Hydrator\HydratorInterface;

class GTEntityHydrator implements HydratorInterface {

    public function extract($object) {
        $result = array();
        $reflectionObject = new \ReflectionObject($object);
        foreach ($reflectionObject->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            $prefix = substr($property->getName(), 0, 3);
            if ($prefix == "_f_") {
                $field = substr($property->getName(), 3);
                $method = "get_" . lcfirst($field);
                $result[$field] = $reflectionObject->getMethod($method)->invoke($object);
            }
        }
        return $result;
    }

    public function hydrate(array $data, $object) {
        $methods = get_class_methods($object);
        foreach ($data as $key => $value) {
            $method = "set_" . lcfirst($key);
            if (in_array($method, $methods)) {
                $object->$method($value);
            }
        }
        return $object;
    }

}

?>
