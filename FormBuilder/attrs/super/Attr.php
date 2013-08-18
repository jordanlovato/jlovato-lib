<?php

class Attr implements IAttr {

    private $attr_name;

    private $attr_value;

    private $field_key;

    public function __construct($field_key, $value, $name = "")
    {
        $this->attr_value = $value;

        $this->field_key = $field_key;

        if (empty($name)) {
            $this->attr_name = stristr(get_class($this), 'Attr', true);
        }
    }

    public function get_attr_name()
    {
        return $this->attr_name;
    }

    public function get_attr_value()
    {
        return $this->attr_value;
    }

    /**
     * @param string $newValue The new value of the attr
     * @return void
     */
    public function set_attr_value($newValue)
    {
        $this->attr_value = $newValue;
    }

    /**
     * @return mixed
     *
     * Default behavior for anonymous attrs. Simply echo $attr_name='$attr_value'.
     * Override this if the IAttr does something fancy
     */
    public function do_attr()
    {
        echo $this->attr_name . "='" . $this->attr_value . "'";
    }

    public function get_associated_field_key()
    {
        return $this->field_key;
    }
}