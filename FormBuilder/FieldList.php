<?php

final class FieldList implements Iterator, Singleton {

    /**
     * @var null
     *
     * Singleton Pattern
     */
    private static $FieldList = null;

    /**
     * @var array
     *
     * An array of all fields in the FieldList
     */
    private static $fields = array();

    /**
     * @var int
     *
     * Iterator for the list
     */
    private static $field_index = 0;

    /**
     * @var null
     *
     * Cached index during a foreach.
     * (because we do $field_key => $field,
     * not $index => $field)
     */
    private static $cached_field_index = null;

    /**
     * @var array
     *
     * A list of active objs that implement javascript dependencies
     */
    private static $dependants = array();

    /**
     * @var array
     *
     * Dependency array for JS
     */
    private static $dep_array = null;

    /**
     * Singleton Pattern
     */
    public function __construct()
    {
        if (isset(self::$FieldList)) {
            throw new BadMethodCallException("Cannot Instantiate new FieldList, one already exists!");
        }

        self::$dep_array = new DependencyArray();
    }

    /**
     * Since we're a singleton, refresh on destruct for a new instance
     */
    public function __destruct()
    {
        self::$FieldList = null;
        self::$fields = null;
        self::$dep_array = null;
        self::$dependants = null;
        self::$field_index = 0 ;
        self::$cached_field_index = null;
    }

    private static function register_dependency(IJsDependency $dependant_obj)
    {
        self::$dependants[$dependant_obj->get_dependency_object_keyword()] = $dependant_obj->get_dependency_object_dependency_keyword();
        self::refresh_dependencies();
    }

    private static function unregister_dependency($dependant_obj_key)
    {
        if (isset(self::$dependants[$dependant_obj_key])) {
            unset(self::$dependants[$dependant_obj_key]);
            self::refresh_dependencies();
        } else {
            throw new InvalidArgumentException("No active dependency for key: $dependant_obj_key");
        }
    }

    public static function refresh_dependencies()
    {
        foreach (self::$dependants as $dependant) {
            $dependencies = $dependant->get_dependencies();

            foreach ($dependencies as $keyword => $dependency_keyword) {
                self::$dep_array->add($keyword, $dependency_keyword);
            }

        }
    }

    /**
     * @param array $field_list
     * @param bool $return
     * @return array
     *
     * Provide a new field_list array to iterate over and manage.
     */
    public static function swap_field_list(Array $field_list, $return = true)
    {
        $old_list = self::get_all_fields();
        self::$fields = $field_list;

        foreach ($field_list as $field) {
            if (is_a($field, 'IJsDependency')) {
                $attrs = $field->get_attrs();
                $decs = $field->get_decorators();

                foreach ($attrs as $attr) {
                    if (is_a($attr, 'IJsDependency')) {
                        self::register_dependency($attr);
                    }
                }

                foreach ($decs as $dec) {
                    if (is_a($dec, 'IJsDependency')) {
                        self::register_dependency($dec);
                    }
                }

                self::register_dependency($field);
            }
        }

        if ($return) {
            return $old_list;
        }
    }

    /**
     * @param $pattern
     * @param bool $preserve_indicies
     * @return array
     *
     * Provide a regexp pattern to match again the indicies of the field list.
     * Return a sublist of fields for which the pattern is true.
     */
    public static function export_sublist($pattern, $preserve_indicies = false)
    {
        $all_fields = self::get_all_fields();
        $sub_fields = array();
        $keys = array_keys($all_fields);
        $return_indicies = preg_grep($pattern, $keys);

        foreach ($return_indicies as $index) {
            if ($preserve_indicies) {
                $sub_fields[$index] = $all_fields[$index];
            } else {
                $sub_fields[] = $all_fields[$index];
            }
        }

        return $sub_fields;
    }

    public static function remove_field_by_index($index, $return_field = false)
    {
        if (isset(self::$fields[$index])) {
            $field = null;

            if ($return_field) $field = self::$fields[$index];

            unset(self::$fields[$index]);

            if ($return_field) return $field;
            else return true;
        } else {
            throw new InvalidArgumentException('No field found at index: ' . $index);
        }
    }

    public static function remove_field_by_field_key($field_key, $return_field = false)
    {
        try {
            $index = self::get_field_by_field_key($field_key, 'index');
        } catch (Exception $e) {
            throw $e;
        }

        self::remove_field_by_index($index, $return_field);
    }

    public static function remove_attr($field, IAttr $attr)
    {
        try {
            $field->remove_attr($attr);
        } catch (Exception $e) {
            throw $e;
        }

    }

    public static function remove_decorator(IField $field, IDecorator $dec)
    {
        // Because decorators are bridged with fields,
        // The way we remove them is to first compare
        // our $dec arg with all decorators in the field
        // and then, if it exists, use THAT object to de-assign
        // it from the field because the decorator define the
        // de-assignment algorithm.
        $field_decorators = $field->get_decorators();
        $field_key = $field->get_field_key();

        foreach ($field_decorators as $d) {
            if ($d == $dec) {
                try {
                    $d->remove_decorator($field_key);
                } catch(Exception $e) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param IField $new_field
     * @param $index
     * @param bool $preserve_meta
     *
     * Update a field by an actual index.
     * Does not interrupt self::$field_index or self::$cached_field_index.
     */
    public static function update_field_by_index(IField $new_field, $index, $preserve_meta = false)
    {
        if (isset(self::$fields[$index])) {
            $field = self::get_field_by_index($index);
            $attrs = $field->get_attrs();
            $decs = $field->get_decorators();

            if ($preserve_meta) {
                foreach ($attrs as $attr) {
                    $new_field->update_attr($attr);
                }

                foreach ($decs as $dec) {
                    $dec->assign_decorator( array($field->get_field_key()) );
                }

            } else if (is_a($field, 'IJsDependency')) {
                foreach ($attrs as $attr) {
                    self::unregister_dependency($attr);
                }

                foreach ($decs as $dec) {
                    self::unregister_dependency($dec);
                }

                self::unregister_dependency($field);
            }

            unset(self::$fields[$index]);
        }

        if (is_a($new_field, 'IJSDependency')) {
            self::register_dependency($new_field);
        }

        self::$fields[$index] = $new_field;
    }

    /**
     * @param IField $new_field
     * @param bool $preserve_meta
     * @throws BadMethodCallException
     *
     * Delete the field if it exists, add the new field to the FieldList.
     */
    public static function update_field(IField $new_field, $preserve_meta = false)
    {
        if (property_exists(get_class($new_field), 'field_key')) {
            $query_field_key = $new_field->get_field_key();
            $FieldList = self::get_instance();

            if (isset($input)) {
                $FieldList = $input;
            }

            // If this field is already in the FieldList, it should have the same $field_key
            foreach ($FieldList as $field_key => $field_vars) {
                if ($query_field_key == $field_key) {
                    if ($preserve_meta) {
                        $attrs = self::$fields[self::get_cached_index()]->get_attrs();
                        $decs = self::$fields[self::get_cached_index()]->get_decorators();

                        foreach ($attrs as $attr) {
                            $new_field->update_attr($attr);
                        }

                        foreach ($decs as $dec) {
                            $dec->assign_decorator(array($field_key));
                        }
                    }

                    self::unregister_dependency(self::$fields[self::get_cached_index()]);
                    unset(self::$fields[self::get_cached_index()]);
                }
            }

            self::add_field($new_field, !$preserve_meta);
        } else {
            throw new BadMethodCallException("Call to FieldList::update_field() on
                class ".get_class($new_field)." that has no field_key.");
        }
    }

    /**
     * @param IField $field
     * @param Bool $register_meta
     *
     * Add a field to the FieldList Obj
     */
    private static function add_field(IField $field, $register_meta = true)
    {
        self::$fields[self::$field_index]['object'] = $field;

        if (is_a($field, 'IJsDependency')) {
            self::register_dependency($field);
        }

        foreach ($field->get_attrs() as $attr) {
            if (is_a($attr, 'IAttr')) {
                self::$fields[self::$field_index][$attr->get_attr_name()] = $attr->get_attr_value();

                if (is_a($attr, 'IJsDependency') && $register_meta) {
                    self::register_dependency($attr);
                }
            }
        }

        foreach ($field->get_decorators() as $dec) {
            if (is_a($dec, 'IDecorator')) {
                self::$fields[self::$field_index][get_class($dec)] = $dec;

                if (is_a($dec, 'IJsDependency') && $register_meta) {
                    self::register_dependency($dec);
                }
            }
        }

        self::$field_index++;
    }

    /**
     * @return array
     */
    public static function get_all_fields() {
        return self::$fields;
    }

    /**
     * @param $attr_name
     * @param $attr_value
     * @return Array
     *
     * Get the indicies of all fields specified by the attr name and value
     */
    public static function get_field_indicies_by_attr($attr_name, $attr_value)
    {
        $indicies = array();
        try {
            $indicies = array_keys(self::get_fields_by_attr($attr_name, $attr_value));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $indicies;
    }

    /**
     * @param IAttr $attr
     * @return Array
     *
     * Get the indicies of all fields specified by the attr obj
     */
    public static function get_field_indicies_by_attr_obj(IAttr $attr)
    {
        $indicies = array();
        try {
            $indicies = array_keys(self::get_fields_by_attr_obj($attr));
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        return $indicies;
    }

    /**
     * @param IAttr $attr
     * @param Bool $preserve_index
     * @return Array
     * @throws InvalidArgumentException
     *
     * get a list of all fields specified by the attr obj
     */
    public static function get_fields_by_attr_obj(IAttr $attr, $preserve_index = true)
    {
        $query_class = get_class($attr);
        $query_value = $attr->get_attr_value();
        $return_fields = array();

        foreach(self::$fields as $field_index => $field_vars) {
            if ($field_vars[$query_class] == $query_value) {
                if ($preserve_index)
                    $return_fields[$field_index] = $field_vars['object'];
                else
                    $return_fields[] = $field_vars['object'];
            }
        }

        if (empty($return_fields)) {
            throw new InvalidArgumentException(
                "Could not find field with attr name: " . $attr->get_attr_name()
                    . " and attr value: " . $attr->get_attr_value()
            );
        }

        return $return_fields;
    }

    /**
     * @param $attr_name
     * @param $attr_value
     * @param Bool $preserve_index
     * @return array
     *
     * get a list of all fields by the specified attr name and attr value
     */
    public static function get_fields_by_attr($attr_name, $attr_value, $preserve_index = true)
    {
        $classname = "Attr".ucfirst($attr_name);
        if (class_exists($classname) && class_implements('IAttr')) {
            $attr = new $classname($attr_value, $attr_name);
        } else {
            $attr = new Attr($attr_value, $attr_name);
        }

        return self::get_fields_by_attr_obj($attr, $preserve_index);
    }

    /**
     * @param $return_index
     * @param String $return_type
     * @return mixed
     * @throws InvalidArgumentException
     *
     * get a field by its absolute index
     */
    public static function get_field_by_index($return_index, $return_type = 'object')
    {
        if (isset(self::$fields[$return_index])) {
            if ($return_type == 'object') {
                return self::$fields[$return_index]['object'];
            } else if ($return_type == 'array') {
                return self::$fields[$return_index];
            } else if ($return_type == 'field_key') {
                return self::$field[$return_index]['object']->get_field_key();
            } else {
                return self::$fields[$return_index]['object'];
            }
        }

        throw new InvalidArgumentException('No field found at index: ' . $return_index);
    }

    /**
     * @param $query_field_key
     * @param String $return_type
     * @return mixed
     * @throws InvalidArgumentException
     *
     * get a field by its field key
     */
    public static function get_field_by_field_key($query_field_key, $return_type = 'object')
    {
        $field_list = self::get_instance();

        foreach ($field_list as $field_key => $field_vars) {
            if ($query_field_key == $field_key) {
                if ($return_type == "object") {
                    return $field_vars['object'];
                } else if ($return_type == "array") {
                    return $field_vars;
                } else if ($return_type == "index") {
                    return self::get_cached_index();
                } else {
                    return $field_vars['object'];
                }
            }
        }

        throw new InvalidArgumentException('No field found at field_key: ' . $query_field_key);
    }

    /**
     * @return null
     *
     * get the last cached index, used when iterating the FieldList object
     */
    private static function get_cached_index()
    {
        return self::$cached_field_index;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return self::$fields[self::$field_index];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        self::$field_index++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $field = self::$fields[self::$field_index];
        self::$cached_field_index = self::$field_index;
        return $field->field_key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return (self::$field_index < count(self::$fields));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        self::$field_index = 0;
    }

    /**
     * @return FieldList|null
     *
     * Singleton Pattern
     */
    public static function get_instance()
    {
        if (self::$FieldList == null) {
            self::$FieldList = new FieldList();
        }

        return self::$FieldList;
    }
}