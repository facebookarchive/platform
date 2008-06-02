<?php

/******BEGIN LICENSE BLOCK*******
* 
* Common Public Attribution License Version 1.0.
*
* The contents of this file are subject to the Common Public Attribution 
* License Version 1.0 (the "License") you may not use this file except in 
* compliance with the License. You may obtain a copy of the License at
* http://developers.facebook.com/fbopen/cpal.html. The License is based 
* on the Mozilla Public License Version 1.1 but Sections 14 and 15 have 
* been added to cover use of software over a computer network and provide 
* for limited attribution for the Original Developer. In addition, Exhibit A 
* has been modified to be consistent with Exhibit B.
* Software distributed under the License is distributed on an "AS IS" basis, 
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License 
* for the specific language governing rights and limitations under the License.
* The Original Code is Facebook Open Platform.
* The Original Developer is the Initial Developer.
* The Initial Developer of the Original Code is Facebook, Inc.  All portions 
* of the code written by Facebook, Inc are 
* Copyright 2006-2008 Facebook, Inc. All Rights Reserved.
*
*
********END LICENSE BLOCK*********/


// Facebook Copyright 2006 - 2008

/**
 * Data structure for storing a single XML node.  Stores the name
 * of the node, its value (which can be a string or an array of its
 * children nodes), and an associative array of attributes.
 */
class xml_element {
  public $name; // string
  public $value; // string OR array of xml_elements
  public $attrs; // associative array of strings (name => value)
  public function __construct($name, $value, $attrs=array()) {
    $this->name = $name;
    $this->value = $value;
    $this->attrs = $attrs;
  }
}

/**
 * Given a string which is mostly UTF8 but may include some invalid UTF8
 * characters, return a valid UTF8 string, replacing any invalid characters
 * with a ?.
 *
 * @param  $text the original text
 * @return the cleaned up text
 */
function api_xml_strip_bad_utf8($text) {
  if (function_exists('iconv')) {
    return iconv('UTF-8', 'UTF-8//IGNORE', $text);
  } else {
    return preg_replace('/(?:[\xc0-\xdf][^\x80-\xbf])|(?:[\xe0-\xef][\x80-\xbf]?[^\x80-\xbf])|(?:[\xf0-\xf7][\x80-\xbf]{0,2}[^\x80-\xbf])/', '?', $text);
  }
}

/**
 * Render a PHP array to xmlwriter memory. Works with the XSD definition.
 *
 * @param $xml_memory the xmlwriter memory to use (created with xmlwriter_open_memory())
 * @param $name       the name of the array
 * @param $object     the array to render
 * @param $attrs      optional assoc array of attributes (name=>value)
 */
function api_xml2_render_object($xml_memory, $name, $object, $attrs=array()) {
  xmlwriter_start_element($xml_memory, $name);
  foreach ($attrs as $k => $v) {
    xmlwriter_write_attribute($xml_memory, $k, $v);
  }

  if ($object instanceof FQLCantSee) {
    xmlwriter_write_attribute($xml_memory, 'xsi:nil', 'true');
  } else if (is_array($object)) {
    xmlwriter_write_attribute($xml_memory, 'list', 'true');
    if (!empty($object)) {
      foreach ($object as $k => $v) {
        if (isset($GLOBALS['api_10_xsd_elt_'.$name])) {
          $v_name = $GLOBALS['api_10_xsd_elt_'.$name];
        } else {
          $v_name = $name . '_elt';
        }
        api_xml2_render_object($xml_memory, $v_name, $v);
      }
    }
  } else if (is_object($object)) {
    foreach ($object as $k => $v) {
      if (isset($v)) {
        api_xml2_render_object($xml_memory, $k, $v);
      }
    }
  } else {
    if (is_bool($object) && !$object) {
      xmlwriter_text($xml_memory, 0);
    } else if (isset($object) && $object !== '') {
      xmlwriter_text($xml_memory, api_xml_strip_bad_utf8($object));
    }
  }
  xmlwriter_end_element($xml_memory);
}

/**
 * Renders an xml_element structure to XMLWriter memory. This
 * basically acts as a wrapper for api_xml2_render_object, first
 * parsing through xml_element layers and then calling api_xml2_render_object
 * to actually render the objects and arrays found.
 *
 * @param $xml_memory the xmlwriter memory to use (created with xmlwriter_open_memory())
 * @param $object     the xml_element to be rendered
 */
function api_xml3_render_object($xml_memory, $object) {
  if (is_array($object->value) && isset($object->value[0]) &&
      $object->value[0] instanceof xml_element) {
    xmlwriter_start_element($xml_memory, $object->name);
    if (isset($object->attrs)) {
      foreach ($object->attrs as $k => $v) {
        xmlwriter_write_attribute($xml_memory, $k, $v);
      }
    }
    foreach ($object->value as $elem) {
      api_xml3_render_object($xml_memory, $elem);
    }
    xmlwriter_end_element($xml_memory);
  } else {
    api_xml2_render_object($xml_memory, $object->name,
                           $object->value, $object->attrs);
  }
}

/**
 * Prints an error response in XML, including the error code,
 * the error message text, as well as all arguments passed in the
 * API request.
 *
 * @param $ec      error code
 * @param $msg     error message text
 * @param $request associative array containing all the parameters
 *                 passed in the request as key-value pairs
 */
function api_xml_render_manual_error($ec, $msg, $request) {
  global $API_DOMAIN_DOT_SUFFIX;

  // FBOPEN:NOTE here, if you are not publishing your own .xsd, to use 'facebook.com' instead
  // of $API_DOMAIN_DOT_SUFFIX

  $print = '';
  $print .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  $print .= '<error_response xmlns="http://api.'.$API_DOMAIN_DOT_SUFFIX.'/1.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://api.'.$API_DOMAIN_DOT_SUFFIX.'/1.0/ http://api.'.$API_DOMAIN_DOT_SUFFIX.'/1.0/facebook.xsd">'."\n";

  $print .= '  <error_code>'.$ec.'</error_code>'."\n";
  $print .= '  <error_msg>'.$msg.'</error_msg>'."\n";
  $print .= '  <request_args list="true">'."\n";

  foreach ($request as $key => $value) {
    $print .= '    <arg>'."\n";
    $print .= '      <key>'.$key.'</key>'."\n";
    $print .= '      <value>'.$value.'</value>'."\n";
    $print .= '    </arg>'."\n";
  }

  $print .= '  </request_args>'."\n";

  $print .= '</error_response>'."\n";

  return $print;
}

