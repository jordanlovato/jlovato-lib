<?php

require_once "IJsDependency.php";
require_once "Singleton.php";
require_once "DependencyArray.php";
require_once "FieldList.php";

define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

spl_autoload_register('include_supers');
spl_autoload_register('include_concretes');

function include_supers() {
    $components = array('attrs', 'decorators', 'fields', 'parse', 'views');
    foreach ($components as $component) {
        $path = ROOT_PATH . $component . DIRECTORY_SEPARATOR . 'super';
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $entry) {
                if ($entry != "." && $entry != "..") {
                    require_once($path . DIRECTORY_SEPARATOR .$entry);
                }
            }
        }
    }
}

function include_concretes() {
    $components = array('attrs', 'decorators', 'fields', 'parse', 'views');
    foreach ($components as $component) {
        $path = ROOT_PATH . $component;
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $entry) {
                if ($entry != "." && $entry != ".." && $entry != "super") {
                    require_once($path . DIRECTORY_SEPARATOR .$entry);
                }
            }
        }
    }
}