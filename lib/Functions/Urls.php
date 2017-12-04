<?php

/**
 * @package     CAPQI
 * @copyright   2017. Erasmus Student Network AISBL.
 * @author      Gorka Guerrero <web-developer@esn.org>
 * @link        http://esn.org
 * @license     
 */

namespace Capqi\Functions;


/**
 * Main API class, it will be extended by all the collections later
 */
class Urls{


  /**
   * Parses an array into minimun information (scheme, host, path..).
   *
   * @param string $url
   *   The url to be parsed
   *
   * @return array
   *   The parsed url in an array
   */
  static public function parse($url){

    return parse_url($url);
  }


  /**
   * Generates an url from the parsed array.
   *
   * @param array $url
   *   The parsed url
   *
   * @return string
   *   The url of the web (root)
   */
  static public function generateUrl($url){

    if (!is_array($url)){
      return "";
    }
    return $url['scheme'].'://'.$url['host'];
  }


  /**
   * Removes the last character ('/' or other) of the string
   *
   * @param string $string
   *   The string we want to remove the last character (usually /)
   *
   * @return string
   *   The string without the last character
   */
  static public function removeLastSlash($string){
    return substr($string, 0, -1);
  }


  /**
   * Checks if the string starts with a specific character (usually /)
   *
   * @param string $string
   *   The string we want to check   
   * @param string $char
   *   The character we want to see if starts with
   *
   * @return bool
   *   True or False depending on the result
   */
  static public function startsWith($string, $char){
    return (substr($string, 0, strlen($char)) === $char);
  }


  /**
   * Checks if the string ends with a specific character (usually /)
   *
   * @param string $string
   *   The string we want to check   
   * @param string $char
   *   The character we want to see if ends with
   *
   * @return bool
   *   True or False depending on the result
   */
  static public function endsWith($string, $char){
    return (substr($string, -strlen($char)) === $char);
  }

}