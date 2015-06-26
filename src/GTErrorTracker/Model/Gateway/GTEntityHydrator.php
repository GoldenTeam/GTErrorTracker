<?php

namespace GTErrorTracker\Model\Gateway;

use Zend\Server\Reflection;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Description of EntityHydrator
 *
 * @author melnik-da
 */
class GTEntityHydrator implements HydratorInterface {

    public function extract($object) {
        $result = array();
        $reflecionObject = new \ReflectionObject($object);
        foreach ($reflecionObject->getProperties(\ReflectionProperty::IS_PRIVATE) as $propiedad) {
            $prefix = substr($propiedad->getName(), 0, 3);
            if ($prefix == "_f_") {
                $field = substr($propiedad->getName(), 3);
                $method = "get_" . lcfirst($field);
                $result[$field] = $reflecionObject->getMethod($method)->invoke($object);
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
