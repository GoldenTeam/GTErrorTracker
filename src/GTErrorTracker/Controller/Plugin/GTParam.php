<?php

namespace GTErrorTracker\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Description of Param
 *
 * @author kalin-mv
 */
class GTParam extends AbstractPlugin {

    public function __invoke($param = null, $default = null) {
        $controller = $this->getController();
        $value = "";
        if ($param != null) {
            $value = $controller->params()->fromRoute($param, -1);
            if ($value === -1) {
                $value = $controller->params()->fromPost($param, -1);
                if ($value === -1) {
                    $value = $controller->params()->fromQuery($param, -1);
                    if ($value === -1) {
                        $value = $default;
                    }
                }
            }
        } else {
            $route = $controller->params()->fromRoute();
            $post = $controller->params()->fromPost();
            $query = $controller->params()->fromQuery();
            $value = array_merge($route, $post);
            $value = array_merge($value, $query);
        }
        return $value;
    }

}

?>
