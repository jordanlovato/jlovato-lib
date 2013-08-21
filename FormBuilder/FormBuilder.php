<?php

require_once "includes.php";

/**
 * Class FormBuilder
 *
 * Engineer for the forms
 */
class FormBuilder {

    private $current_view;

    private $action = "";

    private $method = "post";

    private $arg_attrs = array();

    public function __construct(IView $view, Array $field_list = array(), $form_args = array())
    {
        $this->current_view = $view;

        if (!empty($field_list))
            FieldList::swap_field_list($field_list);

        if (isset($form_args['method']))
        {
            $this->method = $form_args['method'];
            unset($form_args['method']);
        }

        if (isset($form_args['action']))
        {
            $this->action = $form_args['action'];
            unset($form_args['action']);
        }

        foreach ($form_args as $arg)
        {
            if (is_a($arg, 'IAttr'))
            {
                $this->arg_attrs[] = $arg;
            }
        }
    }

    public function render_field_by_attr(IAttr $query_attr) {
        $fields = FieldList::get_fields_by_attr_obj($query_attr);
        foreach ($fields as $field) {
            $field->render_field();
        }
    }

    public function get_field_names()
    {
        $field_names = array(); $deep_parse = false;
        $fields = FieldList::get_all_fields();

        foreach ($fields as $field) {
            if (strpos($field->get_field_name(), '[') !== false) {
                $deep_parse = true;
            }

            $field_names[] = $field->get_field_name();
        }

        if ($deep_parse) {
            $parse_str = substr(implode('=0&', $field_names), 0, -3);
            $output = array();
            parse_str($parse_str, $output);
            return $output;
        } else {
            return $field_names;
        }
    }

    private function method()
    {
        if (!empty($this->method))
            echo "method='".$this->method."'";
    }

    private function action()
    {
        if (!empty($this->action))
            echo "action='".$this->action."'";
    }

    public function no_form_render()
    {
        $this->current_view->create_view();
    }

    public function render()
    {

        ?>

        <form <?php $this->method(); ?> <?php $this->action(); ?>>
            <?php $this->current_view->create_view(); ?>
        </form>

        <?php

    }

    public function get_head()
    {

    }

    public function update_field(IField $field, $new_meta = array(), $merge_meta = false)
    {
        if (!empty($new_meta)) {
            $decorators = (isset($new_meta['decorators'])) ? $new_meta['decorators'] : null;
            $attrs = (isset($new_meta['attrs'])) ? $new_meta['attrs'] : null;

            $field_key = $field->get_field_key();

            foreach ($decorators as $decorator) {
                if (!is_a($decorator, 'IDecorator'))
                    throw new InvalidArgumentException("Non IDecorator passed to FormBuilder::update_field()");

                $decorator->assign_decorator( array($field_key) );
            }

            foreach ($attrs as $attr) {
                if (!is_a($attr, 'IAttr'))
                    throw new InvalidArgumentException("Non IAttr passed to FormBuilder::update_field()");

                $field->update_attr($attr);
            }

            FieldList::update_field($field, $merge_meta);
        }
    }

    public function update_fields(array $fields, $new_uniform_meta = array(), $merge_meta = false)
    {
        foreach ($fields as $field) {
            if (!is_a($field, 'IField'))
                throw new InvalidArgumentException('Non IField passed to FormBuilder::update_fields()');

            try {
                self::update_field($field, $new_uniform_meta, $merge_meta);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    public function remove_field($field, $return = false)
    {
        try {
            $field_index = "";
            if (is_string($field)) {
                $field_index = FieldList::get_field_by_field_key($field, 'index');
            } else if (intval($field)) {
                $field_index = $field;
            } else if (is_a($field, 'IField')) {
                $field_key = $field->get_field_key();
                $field_index = FieldList::get_field_by_field_key($field_key);
            }

            if (empty($field_index)) {
                throw new InvalidArgumentException( "Could not find field with field_key: " . $field->get_field_key() );
            } else {
                $return = FieldList::remove_field_by_index($field_index, $return);
                return $return;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function remove_fields(array $fields)
    {
        foreach ($fields as $field) {
            if (!is_a($field, 'IField')) {
                throw new InvalidArgumentException("Call to FieldList::remove_fields() on array with non IField");
            }
        }

        foreach ($fields as $field) {
            try{
                $this->remove_field($field);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * @param $xml_file
     * @return FormBuilderParseXML
     */
    public static function define_xml_skeleton($xml_file)
    {
        if (file_exists($xml_file)) {
            FormBuilderParseXML::load_file($xml_file);
            $field_list = FormBuilderParseXML::get_field_list();
            return $field_list;
        }
    }
}