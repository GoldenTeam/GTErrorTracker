<?php

namespace GTErrorTracker\Controller\Plugin;

use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadScript;

class GTHead extends AbstractPlugin implements ServiceLocatorAwareInterface {

    /**
     * @var HeadScript HeadScript to adding JS and CSS from plugin.
     */
    private $_serviceManager;
    
    /**
     * @var HeadLink
     */
    private $_headLink;

    /**
     * @var HeadScript
     */
    private $_headScript;
    
    /**
     * Array key values for passing to client side.
     * All options available from Main class (main.js).
     * 
     * @var array 
     */
    private $_options = array();
    
    /**
     *
     * @var array 
     */
    private $_strings = array();

    public function __invoke($method, $parameter = null) {
        if ($parameter == null) {
            $this->$method();
        }  else {
            $this->$method($parameter);
        }
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->_serviceManager = $serviceLocator;
        $manager = $this->_serviceManager->getServiceLocator()->get('ViewHelperManager');
        $this->_headLink = $manager->get('HeadLink');
        $this->_headScript = $manager->get('HeadScript');
    }

    public function getServiceLocator() {
        return $this->_serviceManager;
    }

    /*
     * include CSS files to web page.
     */
    private function include_css($url_file, $browser = "") {
        if (file_exists(getcwd()."/public".$url_file)) {
            switch($browser) {
                case "IE" :
                    $this->_headLink->appendStylesheet($url_file, array("screen", "print"), "IE", null);
                    break;
                default:
                    $this->_headLink->appendStylesheet($url_file, array("screen", "print"));
                    break;
            }
        }
    }
    
    public function css($fileName) {
        if ($this->_headLink != null && !empty($fileName)) {
            $files = is_array($fileName)?$fileName:array($fileName);
            foreach ($files as $file) {
                $parts = pathinfo($file);
                $ext = empty($parts["extension"])?"css":$parts["extension"];
                $dir = isset($parts["dirname"]) && $parts["dirname"] != "."?$parts["dirname"]."/":"";
                $this->include_css("/css/".$dir.$parts["filename"].".".$ext);     // Include base CSS file
                $this->include_css("/css/".$dir.$parts["filename"].".ie.".$ext, "IE");  // Include base CSS file for IE.
            }
        }
    }

     /*
     * include Java Script files to web page.
     */
    public function js($fileName) {
        if ($this->_headScript != null && !empty($fileName)) {
            $files = is_array($fileName)?$fileName:array($fileName);
            foreach ($files as $file) {
                $parts = pathinfo($file);
                $ext = empty($parts["extension"])?"js":$parts["extension"];
                $dir = isset($parts["dirname"]) && $parts["dirname"] != "."?$parts["dirname"]."/":"";
                $file = "/js/".$dir.$parts["filename"].".".$ext;
                if (file_exists(getcwd()."/public".$file)) {
                    $this->_headScript->appendFile($file); 
                }
            }
        }
    }
    
    /*
     * initialization of Java Script files 
     */
    public function init($init) {
        if (!empty ($init)){
            $script = "";
            $init = is_array($init)?$init:array($init);
            foreach ($init as $value) {
                $script .= "try{" . $value . "}catch(e){alert('TrackMyTruck JS INIT ERROR: '+e)}\n";
            }
            $script = 'jQuery(function () {' . "\n" . $script .'});';
            $this->_headScript->appendScript($script);
        }
    }
    
    public function options($options) {
        if (isset($options["strings"])) {
            throw new \Exception('You can use "strings" key in options array. Please use another name.');
        }
        if (!empty ($options) && is_array($options)){
            $this->_options = ArrayUtils::merge($this->_options, $options);
        }
    }
    
    public function strings($strings) {
        if (!empty ($strings) && is_array($strings)){
            $this->_strings = ArrayUtils::merge($this->_strings, $strings);
        }
    }
    
    public function main() {
        $config = $this->_serviceManager->getServiceLocator()->get('config');
        if (isset($config["nutrition_strings"])) {
            $this->_strings = ArrayUtils::merge($this->_strings, $config["nutrition_strings"]);
        } 
        if (count($this->_strings) > 0) {
            $translator = $this->_serviceManager->getServiceLocator()->get('translator');
            foreach ($this->_strings as $key => $value) {
                $this->_strings[$key] = $translator->translate($value);
            }
            $this->_options["strings"] = $this->_strings;
        }
        $user = new Container('user');
        
        $options = Json::encode($this->_options);
        $script = 'jQuery(function () { gtm.init('.$options.'); });';
        $this->_headScript->prependScript($script);
    }
}

?>
