<?php

abstract class Field implements IField {

    private $attrs = array();

    private $decorators = array();

    private $field_key;

    private static $active_field_types = array();

    private static $field_count = 0;

    abstract public function render_field();

    abstract public function get_field_type();

    public function __construct($field_key, Array $init_attrs = array())
    {
        $this->field_key = $field_key;

        // Create a default Name, Class, and ID attrs
        $default_attrs = array(
            new ClassAttr(array('fb-field', $this->get_field_type()), 'class'),
            new Attr('fb-field_' . self::$field_count, 'id'),
            new Attr($this->get_field_key(), 'name'),
        );

        $init_attrs = array_merge($init_attrs, $default_attrs);

        foreach ($init_attrs as $IAttr) {
            $this->update_attr($IAttr);
        }

        self::$field_count++;
    }

    public function get_field_html()
    {
        ob_flush();
        ob_start();
        $this->render_field();
        $return = ob_get_clean();
        ob_flush();
        return $return;
    }

    public function assign_decorator(IDecorator $dec)
    {
        $this->decorators[] = $dec;
    }

    public function remove_decorator(IDecorator $remove_dec) {
        foreach ($this->decorators as $i => $dec) {
            if ($dec === $remove_dec) {
                unset($this->decorators[$i]);
            }
        }
    }

    public function set_decorators(array $dec_list)
    {
        $this->decorators = $dec_list;
    }

    public function get_decorators()
    {
        return $this->decorators;
    }

    public function do_before_decorators()
    {
        foreach ($this->decorators as $decorator) {
            if ($decorator->get_position() == IDecorator::POSITION_BEFORE)
                $decorator->do_decorator();
        }
    }

    public function do_after_decorators()
    {
        foreach ($this->decorators as $decorator) {
            if ($decorator->get_position() == IDecorator::POSITION_AFTER)
                $decorator->do_decorator();
        }
    }

    public function remove_attr(IAttr $attr, $return = false)
    {
        foreach ($this->get_attrs() as $i => $a) {
            if ($attr == $a) {
                $return_attr = ($return) ? $this->attrs[$i]: null ;

                unset($this->attrs[$i]);

                if ($return) return $return_attr;
            }
        }

        throw new InvalidArgumentException(
            "Could not find attr with name: " . $attr->get_attr_name() . " and value: " . $attr->get_attr_value()
        );
    }

    private function set_new_attr(IAttr $attr)
    {
        $this->attrs[$attr->get_attr_name()] = $attr;
    }

    public function update_attr(IAttr $attr) {
        if (isset($this->attrs[$attr->get_attr_name()])) {
            unset($this->attrs[$attr->get_attr_name()]);
        }

        $this->set_new_attr($attr);
    }

    /**
     * @param $attr
     * @return IAttr
     * @throws InvalidArgumentException
     */
    public function get_attr($attr)
    {
        if (class_exists($attr) && is_a($attr, 'IAttr')) {
            $query_attr_name = $attr->get_attr_name();

            if (isset($this->attrs[$query_attr_name])) {
                return $this->attrs[$query_attr_name];
            }
        } else {
            $query_attr_name = strtolower($attr);

            if (isset($this->attrs[$query_attr_name])) {
                return $this->attrs[$query_attr_name];
            }
        }

        throw new InvalidArgumentException("Field::get_attr() called for attr: $attr that does not exist");
    }

    public function get_field_name() {
        $name = $this->get_attr('name');
        return $name;
    }

    public function get_attrs()
    {
        $attrs = $this->attrs;
        return $attrs;
    }

    public function do_attr($attr_name) {
        foreach ($this->get_attrs() as $attr) {
            if ($attr->get_attr_name() == $attr_name) {
                $attr->do_attr();
            }
        }
    }

    public function do_attrs($exceptions = array())
    {
        foreach ($this->attrs as $attr) {
            if (!in_array($attr->get_attr_name(), $exceptions)) {
                $attr->do_attr();
            }
        }
    }

    public function do_label()
    {
        $label = $this->get_decorators();

        $label->do_attr();
    }

    public function get_field_key() {
        $field_key = $this->field_key;
        return $field_key;
    }

    public static function get_active_field_types() {
        $field_types = self::$active_field_types;
        return $field_types;
    }
}