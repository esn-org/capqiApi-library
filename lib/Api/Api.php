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

//Default quantity of items per page. Can be overwritten later in the function
//It is defined in the Employers.php file and if not, then defined here
defined('PAGE_ITEMS') or define('PAGE_ITEMS', 25);

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
   * Allowed collections we can get a response from
   *
   * @var array
   */
  protected $supported_collections = array('employers', 'employer', 'sectors');

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
   * Sets the debug values.
   *
   * @param array|string  $values
   *   Array of values to be debugged   
   *
   * @return string 
   *   The debug message
   */
  public function setDebugMsg($values){

    return $this->auth->setDebugMsg($values);
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
   * Does a GET request to the API to get the list of employers (PAGE_ITEMS) in one page.
   *
   * @param int  $page
   *   The page we want to get the list of employers  
   * @param int  $items
   *   The number of items (employers) we want to get. Default value = PAGE_ITEMS 
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  protected function getSinglePageList($page, $items){

    //Check if parameter is correct
    if ($page < 1){
      return $this->setError('Page cannot be lower than 1.');
    }

    $url = $this->endpoint.'?per='.$items;
    if ($page != 1){
      //If the page is not the first, we have to specify the page parameter in the url
      $url .= '&page='.$page;
    }
    return $this->makeRequest($url, [], 'GET', TRUE);
  }


  /**
   * Does a GET request to the API to get the list of all employers, looping through all the pages.
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  protected function getFullList(){
    
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
      $pageResponse = $this->getSinglePageList($i, PAGE_ITEMS);
      //Get the data from the page response to add to the main response
      if ($pageResponse['type'] == 'response' && $pageResponse['data'] != NULL){
        $response['total'] += $pageResponse['total'];
        $response['data']  = array_merge($response['data'], $pageResponse['data']);
        //Update the loop values ($i) 
        //and check if we meet the condition (items lower than value) to finish the loop ($stop)
        $i++;
        if($pageResponse['page_info']['page'] == $pageResponse['page_info']['total_pages']){
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
  protected function genericGet($id = ''){

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
   * Does a GET request to the API to get one item.
   *
   * @param array   $data
   *   The id of the item we want to get from the API
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  protected function genericPost($name = '', $sector = '', $country = ''){

    if ($name == '' || $sector == '' || $country == ''){
      //We dont allow this function witn an empty $id. That is only for the list of employers
      $response = [
        'type'  => 'response',
        'total' => 0,
        'data'  => [],
      ];
      return $this->_setResponse($response);

    } else {

      $data = [
        'employer' => [
          'name'      => $name,
          'sector_id' => $sector,
          'locations_attributes' => ['country_code' => $country],
        ],
      ];

      //We get an $id, so we get this employer
      $url = $this->endpoint;
      return $this->makeRequest($url, $data, 'POST');
    }
  }


  /**
   * Does a GET request to the API to get the generic page (no endpoint).
   *
   * @return array
   *   An array with the information gotten from the API call
   */
  protected function genericFullGet(){

    //This is the GET request to the normal path (no endpoint)
    $url = $this->endpoint;
    return $this->makeRequest($url);
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
  private function makeRequest($endpoint, $parameters = array(), $method = 'GET', $paginated = FALSE){
    //We set the start of the endpoint
    $url    = $endpoint;
    $method = strtoupper($method);
    $data   = [];

    //It can be a GET with some parameters (i.e. search)
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
      $data = $parameters;
    }

    //We set different CURL options depending if it is a GET or a POST
    if ($method == 'GET'){
      $options = $this->auth->setCurlGetOptions($url);  
    
    } else if ($method == 'POST'){
      //TODO:: should we accept PUT and PATCH too as methods??
      //Or control we have the data at least?
      if(empty($data)){
        $response = $this->setError('Empty data in POST request');
        return $this->_setResponse($response);
      }

      //We need the data because is a POST
      $options = $this->auth->setCurlPostOptions($data, $url);

    } else {
      //Throw exception ??
    }

    print_r($this->setDebugMsg(['endpoint'=>$url]));
    //We got the response. We create a new array that returns an extra bit more of information than the general response
    //i.e: the total of returned elements
    $body = $this->auth->executeCurl($options);

    if (!empty($body)){
      if(!isset($body['errors'])){
        //Respone is fine, no errors
        if (!$paginated){
          //Normal response, not the list of employers (paginated)
          if (in_array(key($body), $this->supported_collections) && $body[key($body)] != NULL ){

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
          //This is the response for the list of employers. It's different what we get from the API, so we need this else
          if ($body['page'] > $body['total_pages']){

            $response = $this->setError('Page requested doesn\'t exist.');
          } else {

            $response = [
              'type'      => 'response',
              'total'     => count($body['records']),
              'data'      => $body['records'],
              'page_info' => [
                'page'             => $body['page'],
                'total_pages'      => $body['total_pages'],
                'total_records'    => $body['total_records'],
                'records_per_page' => $body['records_per_page'],
              ],
            ];
          }
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