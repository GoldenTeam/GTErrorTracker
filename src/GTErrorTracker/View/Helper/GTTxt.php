<?php

namespace GTErrorTracker\View\Helper;

use GTErrorTracker\Model\GTBaseEntity;

use Zend\View\Helper\AbstractHelper;

class GTTxt extends AbstractHelper {
    
    /**
     * To translate and draw text on view, you can pass any text, filed of class View or
     * array key where helper can find the text. If you want to cancel text finding in View 
     * just pass 'false' value to $obj parameter.
     * 
     * @param string $key 
     *        it can be any text or name of View field or array key.
     * @param mixed $obj 
     *        it can be array or "false" value. 
     */
    public function __invoke($key, $obj = null) {
        $text = "";
        $obj = ($obj == null) ? $this->view : $obj;
        if (is_array($obj) && isset($obj[$key])) {  // Find text in array that passed to $obj param.
            $text = $obj[$key];
        } else if (is_object($obj) && isset($obj->$key)) { // Find text in View.
            $text = $obj->$key;
        } else if($obj instanceof GTBaseEntity) {
            $method = "get_" . $key;
            if (method_exists($obj, $method)) {
                $text = $obj->$method();
            }
        } else if (is_string($key)){ // using $key value as string ;
            $text = $key;
        }
        // If text is founded, helper draws and translates it.
        if (strlen($text) > 0) {
            $text = $this->view->translate($text);
        }
        echo $this->view->escapeHtml($text);
    }
}

?>
