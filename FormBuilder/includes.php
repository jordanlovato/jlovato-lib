<?php

require_once "IJsDependency.php";
require_once "Singleton.php";
require_once "DependencyArray.php";
require_once "FieldList.php";

define('ROOT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

spl_autoload_register('include_supers');
spl_autoload_register('include_concretes');

function include_supers() {
    $components = array('attrs', 'decorators', 'fields', 'parse');
    foreach ($components as $component) {
        $path = ROOT_PATH . $component . DIRECTORY_SEPARATOR . 'super';
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        require_once($entry);
                    }
                }
                closedir($handle);
            }
        }
    }
}

function include_concretes() {
    $components = array('attrs', 'decorators', 'fields', 'parse');
    foreach ($components as $component) {
        $path = ROOT_PATH . $component;
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        require_once($entry);
                    }
                }
                closedir($handle);
            }
        }
    }
}