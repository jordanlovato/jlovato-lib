<?php

class LabelDecorator extends Decorator implements IXmlInitDecorator {

    private $for;

    private $copy;

    function __construct($position, array $decorator_params)
    {
        if (isset($decorator_params['for']) && !empty($decorator_params['for']))
            $this->for = $decorator_params['for'];

        if (isset($decorator_params['copy']) && !empty($decorator_params['copy']))
            $this->copy = $decorator_params['copy'];

        $html = $this->build_label_decorator($this->for, $this->copy);

        parent::__construct($position, array('html' => $html));
    }

    public function set_decorator_params(array $new_params)
    {
        if (isset($new_params['for']) && !empty($new_params['for']))
            $this->for = $new_params['for'];

        if (isset($new_params['copy']) && !empty($new_params['copy']))
            $this->copy = $new_params['copy'];

        $html = $this->build_label_decorator($this->for, $this->copy);

        parent::set_decorator_params(array('html' => $html));
    }

    private function build_label_decorator($for, $copy)
    {
        $html = "<label for='$for'>$copy</label>\n";
        return $html;
    }

    public static function parse_xml_string($xml_string)
    {
        $args = FormBuilderParseXML::try_attribute_parse($xml_string);
        $position = self::POSITION_BEFORE;

        if (isset($args['position']))
            $position = $args['position'];

        $obj = new LabelDecorator($position, $args);
        return $obj;

    }
}