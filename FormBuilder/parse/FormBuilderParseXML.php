<?php

/**
 * Class FormBuilderParseXML
 * @package Formbuilder
 *
 * This [singleton] class parses xml formed like html
 * and loads a f
 */
class FormBuilderParseXML extends FormBuilderParse implements Singleton {

    /**
     * @param $file_path
     * @throws InvalidArgumentException
     *
     * Try to load the XML File from path
     */
    public static function load_file($file_path)
    {
        libxml_use_internal_errors(true);

        if (file_exists($file_path)) {
            self::$file_handle = simplexml_load_file($file_path);
        }

        if (self::$file_handle == false || self::$file_handle == null) {
            $error_msg = "FormBuilderParseXML could not Open XML file: $file_path";
            foreach (libxml_get_errors() as $error) {
                $error_msg .= "\t" . $error->message;
            }

            throw new InvalidArgumentException($error_msg);
        }

        self::parse();
    }

    /**
     * Parse dat shiiiiieeeet
     * ...
     * Niggggggguuuuuuuuuuuuuh
     */
    private static function parse()
    {

        $fields_xml = null;

        if (isset(self::$file_handle->form->field)) $fields_xml = self::$file_handle->form->field;

        foreach ($fields_xml as $field_xml) {
            $attrs = $field_xml->attributes();

            // Make the Field
            $field_key = (isset($attrs['key'])) ? $attrs['key'] : "";
            $field = FieldFactory::build_new_field(strval($attrs['type']), array(), $field_key);
            $field_key = $field->get_field_key();

            // Create the label if it exists and add it to the field
            $label_copy = strval($field_xml);
            if (isset($label_copy) && !empty($label_copy)) {
                $label = new LabelDecorator(IDecorator::POSITION_BEFORE, array('for' => '', 'copy' => $label_copy));
                $label->assign_decorator(array($field_key));
            }

            // Create any attrs/decorators that exist and add them to the field
            foreach ($attrs as $attr_name => $attr_value) {
                $attr_classname = ucfirst($attr_name) . 'Attr';
                $dec_classname = ucfirst($attr_name) . 'Decorator';
                $meta_obj = null;

                if (class_exists($attr_classname) && is_a($attr_classname, 'IAttr')) {
                    $meta_obj = new $attr_classname($field_key, $attr_value);
                    $field->update_attr($meta_obj);
                } else if (
                    class_exists($dec_classname) &&
                    is_a($dec_classname, 'IXmlInitDecorator') &&
                    is_a($dec_classname, 'IDecorator')
                ) {
                    $meta_obj = $dec_classname::parse_xml_str(strval($attr_value));
                    $meta_obj->assign_decorator(array($field_key));
                } else {
                    $meta_obj = new Attr($field_key, $attr_value);
                    $field->update_attr($meta_obj);
                }
            }

            FieldList::update_field($field);
        }
    }

    /**
     * @param $xml_string
     * @return array
     *
     * Utility function. Parses an arg string to an array
     * EX: arg1=val1&arg2=val2&arg3=arg3
     */
    public static function try_attribute_parse($xml_string)
    {
        $params = explode('&', trim($xml_string));
        $args = array();

        foreach ($params as $param) {
            $key_value = explode('=', trim($param));
            $key = trim($key_value[0]);
            $value = trim($key_value[1]);

            $args[$key] = $value;
        }

        return $args;
    }

    /**
     * @var
     *
     * Singleton Pattern
     */
    private static $instance;

    /**
     * @return FormBuilderParseXML
     *
     * Singleton Pattern
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FormBuilderParseXML(self::$file_handle);
        }

        return self::$instance;
    }

    /**
     * @return FieldList|null
     *
     * Utility method
     */
    static function get_field_list()
    {
        return FieldList::get_all_fields();
    }
}