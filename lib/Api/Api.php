<?php

/**
 * @package     CAPQI
 * @copyright   2017. Erasmus Student Network AISBL.
 * @author      Gorka Guerrero <web-developer@esn.org>
 * @link        http://esn.org
 * @license     
 */

namespace Capqi\Api;

use \Exception;

defined('PAGE_ITEMS') or define('PAGE_ITEMS', 15);

/**
 * Main API class, it will be extended by all the collections later
 */
class Api{

  /**
   * Endpoint for this API collection
   *
   * @var string
   */
  protected $endpoint;

  /**
   * Auth object that contains the url and the access_token
   *
   * @var object
   */
  protected $auth;
  
  /**
   * Allowed parameters we can use in the search function of this collection
   *
   * @var array
   */
  protected $allowedSearchParameters = array();

  /**
   * The response from the API call (ok or error)
   *
   * @var array
   */
  private $_response;


  /**
   * Constructor
   *
   * @param object   $auth
   *   The Auth object with the info for the API calls and CURL methods
   */
  public function __construct($auth){

    $this->auth = $auth;
  }


  /**
   * Sets the debug flag.
   *
   * @param bool $value
   *   The id of the item we want to get from the API
   */
  public function setDebugMode($value){

    $this->auth->setDebugMode($value);
  }


  /**
   * Sets the debug flag.
   *
   * @param array|string  $values
   *   Array of values to be debugged   
   *
   * @return string 
   *   The debug message
   */
  public function debug($values){

    return $this->auth->debug($values);
  }

  /**
   * Does a GET request to the API to get the list of employers (PAGE_ITEMS) in one page.
   *
   * @param int  $page
   *   The page we want to get the list of employers  
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  public function genericGetList($page){

    //Check if parameter is correct
    if ($page < 1){
      return $this->setError('Page cannot be lower than 1.');
    }

    $url = $this->endpoint;
    if ($page != 1){
      //If the page is not the first, we have to specify the page parameter in the url
      $url = $this->endpoint.'?page='.$page;
    }
    return $this->makeRequest($url);
  }


  /**
   * Does a GET request to the API to get the list of all employers, looping through all the pages.
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  public function genericGetFullList(){

    //First page and condition to stop the loop (initial value is false)
    $i = 1;
    $stop = true;
    //Response we will return
    $response = [
      'type'  => 'response',
      'total' => 0,
      'data'  => [],
    ];

    //We loop always till we meet the stop condition
    do {
      //We get the i-position page
      $pageResponse = $this->genericGetList($i);
      //Get the data from the page response to add to the main response
      if ($pageResponse['type'] == 'response' && $pageResponse['data'] != NULL){
        $total = $pageResponse['total'];
        $response['total'] += $total;
        $response['data'] = array_merge($response['data'], $pageResponse['data']);
        //Update the loop values ($i) 
        //and check if we meet the condition (items lower than value) to finish the loop ($stop)
        $i++;
        if($total < PAGE_ITEMS){
          $stop = false;
        } 

      } else {
        //Error in the response, then we stop the execution to avoid infinite loops or unexpected situations
        $stop = false;  
      }
    //Continue the iteration till $stop is false
    } while($stop);

    return $response;
  }


  /**
   * Does a GET request to the API to get one item.
   *
   * @param int   $id
   *   The id of the item we want to get from the API
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  public function genericGet($id = ''){

    if ($id == ''){
      //We dont allow this function witn an empty $id. That is only for the list of employers
      $response = [
        'type'  => 'response',
        'total' => 0,
        'data'  => [],
      ];
      return $this->_setResponse($response);
    } else {
      //We get an $id, so we get this employer
      $url = $this->endpoint.'/'.$id;
      return $this->makeRequest($url);
    }
  }

  /**
   * Does a GET request to the API to search for items depending on the parameters.
   *
   * @param array  $searchParams
   *   Array with all the filters we are going to use in the API call
   *
   * @return array
   *   An array with the information gotten from the API call or with error message if missing parameters
   */
  public function search($searchParams){

    //If empty parameters, error
    if (empty($searchParams)){
      return $this->setError('Search parameters missing.');
    
    } else {
      //If there is a parameter in the search that is not allowed, flag it for later error
      $wrongParam = FALSE;
      foreach (array_keys($searchParams) as $param) {
        if (!in_array($param, $this->allowedSearchParameters)){
          //Yes, found one
          $wrongParam = TRUE;
        }
      }
      if($wrongParam){
        //Incorrect parameters
        return $this->setError('Incorrect parameter in search.');
      }
    }
    //Parameters are ok,let's do the search then
    return $this->makeRequest($this->endpoint.'/search', $searchParams, 'GET');
  }


  /**
   * Returns an error message.
   *
   * @param string  $message
   *   The error message that is in the array we are returning
   *
   * @return array
   *   An array with the error message set
   */
  private function setError($message){

    $error = array(
      'type'    => 'error',
      'message' => $message,
    );

    return $error;
  }


  /**
   * Does the API request to get the requested information from the endpoint
   *
   * @param string   $endpoint
   *   The url of the API we will perform the request to. Example: 'countries', 'cities',...
   * @param array    $parameters 
   *    Optional. An array with some of the parameters we use in the URL (GET) or in the headers (POST)
   *    Default = array()
   * @param string   $method 
   *    Optional. The method we will use (GET / POST).
   *    Default = GET
   *
   * @return array
   *   Array with the response from the API request
   */
  private function makeRequest($endpoint, $parameters = array(), $method = 'GET'){
    //We set the start of the endpoint
    $url    = $endpoint;
    $method = strtoupper($method);
    $data   = [];

    //IT can be a GET with some parameters (i.e. search)
    if (!empty($parameters) && $method == 'GET'){
      $queryArray = [];
      //We loop all the parameters
      foreach ($parameters as $key => $value) {
        $queryArray[] = $key.'='.$value;
      }
      //And make a string from the array
      $query = implode('&', $queryArray);
      //We got the final query ready for the url. We concatenate it
      $url .= '?'.$query;
    }

    //In POST, parameter contains the data for the headers
    if (!empty($parameters) && $method == 'POST'){
      //should we accept PUT and PATCH too as methods??
      foreach ($parameters as $key => $parameter) {
        $data[$key] = trim($parameter);
      }
    }

    if ($method == 'GET'){
      $options = $this->auth->setCurlGetOptions($url);  
    
    } else if ($method == 'POST'){
      //should we accept PUT and PATCH too as methods??
      $options = $this->auth->setCurlPostOptions($data, $url);

    } else {
      //Throw exception ??
    }

    print_r($this->debug(['endpoint'=>$url]));
    //We got the response. We create a new array that returns an extra bit more of information than the general response
    //i.e: the total of returned elements
    $body = $this->auth->executeCurl($options);

    if (!empty($body)){
      if(!isset($body['errors'])){
        //Respone is fine, no errors
        if ( (in_array(key($body), ['employers']) && $body['employers'] != NULL) || 
             (in_array(key($body), ['employer']) && $body['employer'] != NULL)) {

          //We can have one employer or multiple because the endpoint is the same except for the parameter
          //But the response is different, so we need this
          $singleItem = (in_array(key($body), ['employer']) ? true : false );
          $response = [
            'type'  => 'response',
            'total' => ($singleItem ? 1                   : count($body[key($body)])),
            'data'  => ($singleItem ? [$body[key($body)]] : $body[key($body)]),
          ];

        } else {
          $response = $this->setError('Error in API request. Response is empty.');
        }

      } else {
        //Response returns an error from the API
        $response = $this->setError($body['errors']);
      }

    } else {
      //No response, error
      $response = $this->setError('Error during CURL execution.');
    }

    return $this->_setResponse($response);
  }


  /**
   * Store locally in the object and then return the response.
   *
   * @param array  $response
   *   The response from the API call
   *
   * @return array
   *   The response from the API call
   */
  private function _setResponse($response){
    $this->_response = $response;
    return $response;
  }

}