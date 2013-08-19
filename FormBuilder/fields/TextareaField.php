<?php

class TextareaField extends Field
{
    const FIELD_NAME = 'textarea';

    public function render_field()
    {
        ?>

            <?php $this->do_before_decorators(); ?>

            <textarea <?php $this->do_attrs(array('value')); ?> > <?php $this->do_attr('value'); ?> </textarea>

            <?php $this->do_after_decorators(); ?>

        <?php
    }

    public function get_field_type()
    {
        return self::FIELD_NAME;
    }
}