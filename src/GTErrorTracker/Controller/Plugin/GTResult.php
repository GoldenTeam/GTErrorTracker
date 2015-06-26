<?php

namespace GTErrorTracker\Controller\Plugin;

use GTErrorTracker\Model\Gateway\GTBaseTableGateway;
use GTErrorTracker\H;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\JsonModel;

/**
 * Description of Result
 * @author kalin-mv
 */
class GTResult extends AbstractPlugin {

    public function __invoke($data = null) {
        $controller = $this->getController();
        $data = ($data == null ?
            H\GTResult::to() :
            ($data instanceof GTBaseTableGateway ?
                $data->result() :
                $data));
        $request = $controller->getRequest();
        if ($request->isXmlHttpRequest()) {
            $result = array(
                "error" => true,
                "result" => array("message" => "The server has returned empty response.", "code" => 0));
            if (is_array($data) && count($data) > 0) {
                $result["error"] = isset($data["error"]) ? $data["error"] : false;
                unset($data["error"]);
                $result["result"] = isset($data["result"]) ? $data["result"] : $data;
                $result["result"]["code"] = isset($data["code"]) ? $data["code"] : 0;
            } else {
                $result = H\GTResult::to();
            }
            return new JsonModel($result);
        }
        return $data;

    }
}

?>
