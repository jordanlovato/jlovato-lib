<?php

/**
 * Interface XmlInitDecorator
 *
 * Decorators that implement this interface
 * can be defined via an XML skeleton
 * and made with the FormBuilderParseXml obj
 */
interface IXmlInitDecorator {

    public static function parse_xml_string($xml_string);

}