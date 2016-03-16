<?php

namespace GTErrorTracker\Form;

use Zend\Form\Element\Text;
use Zend\Form\Form;

class GTEventSearchForm extends Form
{
    public function __construct($sm)
    {
        parent::__construct('GTEventSearch');

        $element = new Text();
        $element
            ->setName('GTEventData')
            ->setAttribute('class', 'form-control')
            ->setLabel('Error search')
            ->setLabelAttributes(array('class'  => 'col-sm-2 control-label'));
        $this->add($element);


    }
}