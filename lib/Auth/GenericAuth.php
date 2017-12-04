<?php

/**
* @package     CAPQI
* @copyright   2017. Erasmus Student Network AISBL.
* @author      Gorka Guerrero <web-developer@esn.org>
* @link        http://esn.org
* @license     
*/

namespace Capqi\Auth;

use \Exception;
use Capqi\Functions\Urls;

/**
* Generic REST class that connects with the API (For Auth, GET, PUT...)
*/
class GenericAuth{

  /**
   * HTTP Response code returned by the API call
   *
   * @var int
   */
  protected $_httpResponseCode;

  /**
   * Error message returned by the API call if any
   *
   * @var string
   */
  protected $_httpResponseMessage;

  /**
   * Debug mode flag
   *
   * @var bool
   */
  protected $_debug = false;


  /**
   * Getter. Gets the HTTP Response Code returned by the API call after the execution.
   *
   * @return int           
   *   The HTTP Response Code.
   */
  public function getHttpCode(){
    return $this->_httpResponseCode;
  }


  /**
   * Setter. Sets the HTTP Response Code returned by the API call after the execution.
   *
   * @param int $code     
   *   The HTTP Response Code.
   */
  public function setHttpCode($code){
    $this->_httpResponseCode = $code;
  }


  /**
   * Setter. Sets the value for the debug flag.
   *
   * @param bool $value    
   *   The value of the flag.
   */
  public function setDebugMode($value){
    $this->_debug = $value;
  }


  /**
   * Returns an error message to be used as output
   *
   * @param array|string  $values   
   *   Array to output if debug flag is active
   *
   * @return string 
   *   The debug message
   */
  public function debug($values){

    if ($this->_debug){
      //We need an array, but we accept strings as well
      if (!is_array($values)){
        $values = array($values);
      }    
      return 'DEBUG: '.json_encode($values, JSON_UNESCAPED_SLASHES).'<br>';  
    }
  }


  /**
   * Setter. Sets the Response Message (error) returned by the API call after doing an Auth call.
   *
   * @param string $message   
   *   The Error message (if any) returned by the API after doing an Auth call.
   */
  public function setHttpResponseMesagge($message){
    $this->_httpResponseMessage = $message;
  }


  /**
   * Getter. Gets the Response Message (error) returned by the API call after doing an Auth call.
   *
   * @return string 
   *   The Error message (if any) returned by the API after doing an Auth call.
   */
  public function getHttpResponseMesagge(){
    return $this->_httpResponseMessage;
  }


  /**
   * Do an CURL (GET/POST) request defined by the $options set.
   *
   * @param  array $options  
   *   An array with all the different parameters (headers, settings, data...) for the API request.
   *
   * @return array  
   *   An array with the response of the API request
   */
  public function executeCurl($options){

    //Initialize the curl object
    $curl = curl_init();
    //Set the options we have created before and passed by parameter
    curl_setopt_array($curl, $options);
    //Execute and decode the response
    $body = curl_exec($curl);
    $body = json_decode($body, true);
    //Set the response code of the Request
    $this->setHttpCode(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    //Close the conection
    curl_close($curl);
    //Return the body decoded with the response in an array format
    return $body;
  }


  public function getApiHostUrlonly(){
    $url = Urls::parse($this->_url);
    return Urls::generateUrl($url);
  }

}