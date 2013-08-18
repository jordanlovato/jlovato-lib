<?php

interface IAttr {

    public function __construct($field_key, $value);

    public function get_attr_name();

    public function get_attr_value();

    /**
     * @param string $newValue The new value of the attr
     * @return void
     */
    public function set_attr_value($newValue);

    /**
     * @return mixed
     *
     * Do whatever behavior this attr implies
     */
    public function do_attr();

    public function get_associated_field_key();

}