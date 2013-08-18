<?php

interface IDecorator {

    const POSITION_BEFORE = 'before';

    const POSITION_AFTER = 'after';

    public function __construct($position, array $decorator_params);

    public function assign_decorator($field_keys);

    public function remove_decorator($field_keys);

    public function move_decorator($old_field_key, $new_field_key);

    public function get_associated_fields();

    public function get_decorator_position();

    public function set_decorator_position($new_position);

    public function set_decorator_params(array $new_params);

    public function set_new_html($html);

    public function do_decorator();

}