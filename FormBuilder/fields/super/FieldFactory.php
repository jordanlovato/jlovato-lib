<?php

/**
 * Class FieldFactory
 *
 * This class is in charge of creating IField objects,
 * and pushing them to the FieldList Singleton for management
 */

final class FieldFactory implements Singleton {

    private static $FieldList;

    private static $instance;

    public static $field_count = 0;

    function __construct()
    {
        self::$FieldList = FieldList::get_instance();
    }

    /**
     * @param $field_type
     * @param $init_attrs
     * @param $field_key
     * @return Field
     *
     * Create a new field and add it to the FieldList
     */
    public static function build_new_field($field_type, $init_attrs, $field_key = "")
    {
        if (empty($field_key))
            $field_key = "FIELD_" . ++self::$field_count;

        $field = self::create_field($field_key, $field_type, $init_attrs);
        FieldList::update_field($field);
        return $field;
    }

    /**
     * @param $field_key
     * @param $field_type
     * @param array $init_attrs
     * @return TextareaField|TextField
     *
     * Instantiate a new Field Obj
     */
    private static function create_field($field_key, $field_type, $init_attrs = array())
    {
        switch (strtolower($field_type)) {
            case 'text':
            case 't':
            case 'textfield':
                return new TextField($field_key, $init_attrs);
                break;
            case 'textarea':
            case 'ta':
                return new TextareaField($field_key, $init_attrs);
                break;
            case 'checkbox':
            case 'cb':
                return new CheckboxField($field_key, $init_attrs);
                break;
        }
    }

    /**
     * @param IAttr $query_attr
     * @param $new_field_type
     *
     * Morph a field type via IAttr query
     */
    public function change_field_type_by_attr(IAttr $query_attr, $new_field_type)
    {
        $indicies = FieldList::get_field_indicies_by_attr_obj($query_attr);

        foreach ($indicies as $index) {
            $field_key = FieldList::get_field_by_index($index, 'field_key');
            FieldList::update_field_by_index(self::create_field($field_key, $new_field_type), $index, true);
        }
    }

    /**
     * @param $field_key
     * @param $new_field_type
     *
     * Morph a field type via field_key
     */
    public function change_field_type_by_field_key($field_key, $new_field_type)
    {
        $index = FieldList::get_field_by_field_key($field_key);
        FieldList::update_field_by_index(self::create_field($field_key, $new_field_type), $index, true);
    }

    /**
     * @return FieldFactory
     *
     * Singleton Pattern
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FieldFactory();
        }

        return self::$instance;
    }
}