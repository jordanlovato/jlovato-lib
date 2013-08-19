<?php

class CheckboxField extends Field {

    const FIELD_NAME = 'checkbox';

    public function render_field()
    {
        ?>

            <?php $this->do_before_decorators(); ?>

            <input type="checkbox" <?php $this->do_attrs(); ?> />

            <?php $this->do_after_decorators(); ?>

        <?php
    }

    public function get_field_type()
    {
        return self::FIELD_NAME;
    }
}