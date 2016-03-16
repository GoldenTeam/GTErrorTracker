<?php

namespace GTErrorTracker\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GTHtml extends AbstractHelper {

    public function __invoke($key, $obj = null) {
        $html = "";
        $obj = ($obj == null) ? $this->view : $obj;
        if (is_array($obj) && isset($obj[$key])) {  // Find text in array that passed to $obj param.
            $html = $obj[$key];
        } else if (is_object($obj) && isset($obj->$key)) { // Find text in View.
            $html = $obj->$key;
        } else if (is_string($key)){ // using $key value as string ;
            $html = $key;
        }
        echo ($html);
    }

}

?>
