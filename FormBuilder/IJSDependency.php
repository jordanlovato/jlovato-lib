<?php

interface IJsDependency {

    /**
     * @return string
     *
     * Return a keyword that identifies this object when
     * registering and unregistering it in the FieldList
     */
    public function get_dependency_object_keyword();

    /**
     * @return string
     *
     * Return a keyword that identifies this object when
     * registering and unregistering it in the FieldList
     */
    public function get_dependency_object_dependency_keyword();

    /**
     * @return mixed
     *
     * Plural of what is defined by IJsDependency::get_dependency
     */
    public function get_dependencies();

    /**
     * @param $keyword
     * @return mixed
     *
     * return an array structured as follows:
     * array(
     *  'keyword' => 'dependency_keyword'
     *  'meta' => $meta_data
     * );
     */
    public function get_dependency($keyword);

    /**
     * @param $keyword
     * @return mixed
     *
     * returns just the meta data for a keyword
     */
    public function get_dependency_meta_data($keyword);

}