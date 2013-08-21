<?php

class TwoColumnView implements IView {

    private $left_fields = array();

    private $right_fields = array();

    /**
     * Crawl the FieldList and separate the fields into left and
     * right columns. Then reset the FieldList Singleton.
     */
    private function build_lists() {
        $field_list = self::get_field_list();
        $left_fields = $right_fields = array();

        // Get the odd fields
        $left_fields = FieldList::export_sublist('/^\d*[13579]$/', true);

        // Get the even fields
        $right_fields = FieldList::export_sublist('/^\d*[02468]$/', true);

        // Move the fields with a ClassAttr of 'left' to the left and
        // those with a ClassAttr of 'right' to the right.
        $left_query_attr = new ClassAttr(array('left'));
        $right_query_attr = new ClassAttr(array('right'));

        $old_list = FieldList::swap_field_list($left_fields);
        $add_to_right = FieldList::get_fields_by_attr_obj($right_query_attr);
        $remove_from_left = array_keys($add_to_right);

        FieldList::swap_field_list($right_fields);
        $add_to_left = FieldList::get_fields_by_attr_obj($left_query_attr);
        $remove_from_right = array_keys($add_to_left);

        $left_fields = array_merge($left_fields, $add_to_left);
        foreach ($remove_from_left as $index) {
            unset($left_fields[$index]);
        }

        $right_fields = array_merge($right_fields, $add_to_right);
        foreach ($remove_from_right as $index) {
            unset($right_fields[$index]);
        }

        $this->left_fields = $left_fields;
        $this->right_fields = $right_fields;
        FieldList::swap_field_list($old_list);
    }

    public function create_view()
    {
        $this->build_lists();

        ?>

        <div class="col1">
            <?php

                foreach ($this->left_fields as $field) {
                    $field->render_field();
                }

            ?>
        </div>

        <div class="col2">
            <?php

            foreach ($this->right_fields as $field) {
                $field->render_field();
            }

            ?>
        </div>

    <?php
    }

    private function get_field_list()
    {
        return FieldList::get_instance();
    }
}