<?php
/**
 * Created by Jordan Lovato.
 */

class OneColumnView implements IView {

    public function create_view()
    {
        $fields = FieldList::get_all_fields();

        ?>

        <div class="col">
            <?php foreach ($fields as $field) : ?>
            <?php $field->render_field(); ?>
            <?php endforeach; ?>
        </div>

        <?php

    }
}