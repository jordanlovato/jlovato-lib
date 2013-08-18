<?php

include "Field.php";

class TextField extends Field
{
    const FIELD_NAME = 'text';

    public function get_field_html()
    {
        ob_start();
        $this->render_field();
        return ob_get_clean();
    }

    public function render_field()
    {
        ?>

        <?php $this->do_label(); ?>

        <input type="text" <?php $this->do_attrs(); ?> />

        <?php
    }

    public function get_field_type()
    {
        return self::FIELD_NAME;
    }
}