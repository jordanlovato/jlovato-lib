<?php

interface IField {

    public function get_field_html();

    public function render_field();

    public function remove_attr(IAttr $attr);

    public function update_attr(IAttr $attr);

    public function get_attr($attr);

    public function get_attrs();

    public function assign_decorator(IDecorator $dec);

    public function set_decorators(array $dec_list);

    public function get_decorators();

    public function get_field_key();

    public function get_field_type();

    public function do_attrs();

    public function do_before_decorators();

    public function do_after_decorators();

}