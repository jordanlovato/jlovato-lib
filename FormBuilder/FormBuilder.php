<?php
/**
 * Created by Jordan Lovato.
 * This class builds and manages form fields from XML.
 */

class FormBuilder {
    protected $mode;
    protected $form;
    protected $fields = array();

    protected $method;
    protected $action;

    public static $parsed_field_attrs = array(
        'name',
        'id',
        'class',
        'default',
        'tooltip',
        'hint',
    );

    protected $flags = array();
    protected $names_list = array();

    public function __construct($xml)
    {
        if (is_a($xml, 'SimpleXMLElement')) {
            $this->parse_form($xml);
            $this->parse_fields($xml);
        } else {
            if (file_exists($xml)) {
                $xml = simplexml_load_file($xml);
                $this->parse_form($xml);
                $this->parse_fields($xml);
            }
        }
    }

    public function render_form()
    {
        $fields = $this->fields;
        $method = $this->method;
        $action = $this->action;

        include("templates/form.php");
    }

    private function parse_fields(SimpleXMLElement $xml)
    {
        $fields_xml = array(); $i = 0;
        if (isset($xml->form->field)) $fields_xml = $xml->form->field;

        foreach ($fields_xml as $field_xml) {
            $attrs = $field_xml->attributes();
            // the 'type' attr MUST exist
            $this->fields[$i]['type'] = $this->parse_type($attrs['type'], $i);
            $this->fields[$i]['label'] = $field_xml;

            // Dynamically Build the rest of the list, in case more attrs become standard later
            foreach (self::get_field_attr_list() as $attr_name) {
                $this->flag($i, "hint");
                $this->fields[$i][$attr_name] = (isset($attrs[$attr_name])) ? $attrs[$attr_name] : "";
            }
            $this->generate_field_data($this->fields[$i], $i);
            $i++;
        }
    }

    private function generate_field_data($field, $index)
    {
        if (empty($field['name'])) {
            $field_name = "FB_field-".$index;
            $this->fields[$index]['name'] = $field_name;
        } else {
            $field_name = $field['name'];
        }
        $this->names_list[$index] = strval($field_name);

        if (empty($field['id'])) {
            if (!empty($field['name'])) {
                $id = $field['name'];
            } else {
                $id = "FB_field-".$index;
            }

            $this->fields[$index]['id'] = $id;
        }

        if (empty($fields['class'])) {
            $class = "FB_field FB_field-{$field['type']} FB_field-".$index;
            $this->fields[$index]['class'] = $class;
        }
    }

    private function parse_type($type, $index)
    {
        switch($type) {
            case "date":
                $this->flag($index, $type);
                $type = "div";
                break;
            case "date-range":
                $this->flag($index, $type);
                $type = "div";
                break;
        }

        return $type;
    }

    private function parse_form(SimpleXMLElement $xml)
    {
        $form_xml = $xml->form;
        $form_attrs = $form_xml->attributes();
        foreach ($form_attrs as $k => $attr) {
            switch($k) {
                case "action":
                    $this->action = $attr;
                    break;
                case "method":
                    if (!empty($attr))
                        $this->method = $attr;
                    else
                        $this->method = "post";
                    break;
                case "mode":
                    $this->mode = $attr;
                    break;
            }
        }
    }

    private function flag($i, $type)
    {
        $this->flags[$i] = $type;
    }

    public function get_names_list()
    {
        return $this->names_list;
    }

    public static function get_field_attr_list()
    {
        return self::$parsed_field_attrs;
    }
}