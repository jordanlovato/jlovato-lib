<?php

class TextField extends Field
{
    const FIELD_NAME = 'text';

    public function __construct($field_key, $init_attrs = array()) {
        parent::__construct($field_key, $init_attrs);
    }

    public function render_field()
    {
        ?>

            <?php $this->do_before_decorators(); ?>

            <input type="text" <?php $this->do_attrs(); ?> />

            <?php $this->do_after_decorators(); ?>

        <?php
    }

    public function get_field_type()
    {
        return self::FIELD_NAME;
    }
}