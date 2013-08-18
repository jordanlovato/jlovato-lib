<?php

/**
 * Class Decorator
 *
 * Decorators use the bridge pattern to interface with fields.
 */
class Decorator implements IDecorator {

    private $field_keys = array();

    private $position = self::POSITION_BEFORE;

    private $decorator_params = array();

    private $html = "";

    public function __construct($position, array $decorator_params)
    {
        if ($position == self::POSITION_AFTER || $position == self::POSITION_BEFORE)
            $this->position = $position;

        $this->set_decorator_params($decorator_params);
    }

    public function set_decorator_params(array $new_params)
    {
        if (isset($new_params['html']) && !empty($new_params['html']))
            $html = $new_params['html'];

        $this->decorator_params = $new_params;
    }

    public function assign_decorator($field_keys)
    {
        if (!is_array($field_keys)) $field_keys = array($field_keys);
        $this->field_keys = array_merge($field_keys, $this->field_keys);

        foreach ($this->field_keys as $field_key) {
            $field = FieldList::get_field_by_field_key($field_key);
            $field->assign_decorator($this);
        }
    }

    public function remove_decorator($field_keys)
    {
        foreach ($this->field_keys as $i => $field_key) {
            if (in_array($field_key, $field_keys)) {
                unset($this->field_keys[$i]);
                $field = FieldList::get_field_by_field_key($field_key);
                $field->remove_decorator($this);
            }
        }
    }

    public function move_decorator($old_field_key, $new_field_key)
    {
        foreach ($this->field_keys as $i => $field_key) {
            if ($old_field_key == $field_key) {
                $this->field_keys[$i] = $new_field_key;

                $old_field = FieldList::get_field_by_field_key($old_field_key);
                $old_field->remove_decorator($this);

                $new_field = FieldList::get_field_by_field_key($new_field_key);
                $new_field->assign_decorator($this);
            }
        }
    }

    public function get_decorator_position()
    {
        return $this->position;
    }

    public function set_decorator_position($new_position)
    {
        if ($new_position == self::POSITION_AFTER || $new_position == self::POSITION_BEFORE)
            $this->position = $new_position;
    }

    public function clone_decorator($new_field_keys) {
        $new_decorator = clone $this;
        $new_decorator->remove_decorator($new_decorator->get_associated_fields());
        $new_decorator->assign_decorator($new_field_keys);
    }

    public function do_decorator()
    {
        echo $this->decorator_params['html'];

        if (!isset($this->decorator_params['html']))
            throw new BadMethodCallException(
                substr('No HTML set for decorator assigned to fields: ' . implode(', ', $this->field_keys), 0, -2)
            );
    }

    public function get_associated_fields()
    {
        return $this->field_keys;
    }

    public function set_new_html($html)
    {
        $this->html = $html;
    }
}