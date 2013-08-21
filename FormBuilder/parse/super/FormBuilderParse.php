<?php

abstract class FormBuilderParse {

    protected static $file_handle;

    private static $instance;

    function __construct($file_handle)
    {
        self::$file_handle = $file_handle;
    }
}