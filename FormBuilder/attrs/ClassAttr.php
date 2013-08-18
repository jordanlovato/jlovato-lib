<?php

class ClassAttr extends Attr {

    private $class_list = array();

    const ATTR_NAME = 'class';

    public function __construct($field_key, $classes = array()) {
        $this->class_list = $classes;
        parent::__construct($field_key, $classes, self::ATTR_NAME);
    }

    public function set_attr_value($newValue) {
        $this->add_classes($newValue);
    }

    public function add_classes(Array $classes) {
        $this->class_list = array_merge($this->class_list, $classes);
    }

    public function do_attr() {
        $class_str = substr(explode(' ', $this->class_list), 0, -1);
        echo "class='".$class_str."'";
    }
}