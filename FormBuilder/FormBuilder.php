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

    private $method = "";

    private $arg_attrs = array();

    public function __construct(IView $view, Array $field_list = array(), $form_args = array())
    {
        $this->current_view = $view;
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

    /**
     * @param $xml_file
     * @return FormBuilderParseXML
     */
    public static function define_xml_skeleton($xml_file)
    {
        if (file_exists($xml_file)) {
            $parser = new FormBuilderParseXML($xml_file);
            $field_list = $parser->get_field_list();
            return $field_list;
        }
    }
}