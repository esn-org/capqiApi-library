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

/**
* Basic Authentication Class
*/
class BasicAuth extends GenericAuth{

  /**
   * Email to login the API
   *
   * @var string
   */
  private $_email;

  /**
   * Password associated with the email
   *
   * @var string
   */
  private $_password;

  /**
   * URL where the API located and we have to call it
   *
   * @var string
   */
  public $_url;

  /**
   * Token is returned if the Authetication is success
   *
   * @var string
   */
  protected $_access_token;

  /**
   * Endpoint for this API collection
   *
   * @var string
   */
  protected $_endpoint = 'sign_in';


  /**
   * Constructor that is called by the ReflectionMethod
   * 
   * @param string $mail             
   *   The mail to use for Authentication, required
   * @param string $password         
   *   The password to use for Authentication, required
   * @param string $url              
   *   The url where the API is located   
   * @param string $access_token     
   *   The access_token we use for the API Requests (if any)  
   *
   * @throws Exception
   * @return BasicAuth object
   */
  public function initialize($email, $password, $url, $access_token = null) {
    //mEmail and password are mandatory, not the URL. But we trim them before doing anything
    $email    = trim($email);
    $password = trim($password);
    $url      = trim($url);

    //Check if we have the mail and password for doing the login
    if (empty($email) || empty($password)) {
      //Throw exception if the required parameters were not found
      throw new Exception('One or more required parameters was not supplied. Both email and password required!');
    }
    //All good, we store the vars in the object vars
    $this->_email    = $email;
    $this->_password = $password;
    $this->_url      = $url;

    if(!empty($access_token)){
      //We have stored (somehow) the token from a previous call.

      //TODO check with IGP: there should be a checker in the API for the access_token
      $this->_access_token = $access_token;
      $this->setHttpCode(200);
      $this->setHttpResponseMesagge('Logged with stored valid token');

    } else {
      //We need to get the token because we dont have it. We prepare the data for the POST request
      $data = [
        'email'    => $this->_email,
        'password' => $this->_password,
      ];
      //Set the options for the request (with the data created) and passing the endpoind for the Auth collection
      $options = $this->setCurlPostOptions($data, $this->_endpoint);
      //Execute the call
      $body    = $this->executeCurl($options);
      
      //We got response, see if was successful or not.
      if ($this->isValidAuth() && isset($body['access_token'])){
        //We got the new access_token
        $this->_access_token = $body['access_token'];
        $this->setHttpResponseMesagge('Logged successfuly using mail/pass via API');
      } else {
        //We got an error (and we store it)
        $this->setHttpResponseMesagge($body['errors']);
      }
    }
  }


  /**
   * Returns if the Auth has executed perfectly or there was an error
   * 
   * @return bolean  
   *   True if code 200, False if other code
   */
  public function isValidAuth(){

    //We check if the HTTP code is 200 (ok)
    if ($this->getHttpCode() == 200){
      return 1;
    } else {
      //Or other (error)
      return 0;
    }
  }


  /**
   * Getter. Gets the API base url.
   *
   * @return string 
   *   The API base url.
   */
  public function getApiUrl(){
    return $this->_url;
  }


  /**
   * Getter. Gets the API email linked to the logged user.
   *
   * @return string 
   *   The API base url.
   */
  public function getApiMail(){
    return $this->_email;
  }


  /**
   * Getter. Gets the API access_token linked to the logged user.
   *
   * @return string
   *   The API access token linked to the logged user.
   */
  public function getApiToken(){
    return $this->_access_token;
  }


  /**
   * Set the options (headers, data..) for a GET request using CURL
   *  
   * @param string $endpoint     
   *   The endpoint of the API collection we will call  
   *
   * @return array               
   *   The array with all the options
   */
  public function setCurlGetOptions($endpoint = ''){
    //If we dont specify the endpoint, we use the local one of the object (We use this function for all the requests)
    $endpoint = ($endpoint == '' ? $this->_endpoint : $endpoint);
    //Set all the options
    $options[CURLOPT_URL]            = $this->getApiUrl().$endpoint; //Concatenate endpoint to the url we already have
    $options[CURLOPT_RETURNTRANSFER] = true;
    //IT is supposed we have a token, otherwise we will get an error
    $options[CURLOPT_HTTPHEADER]     = $this->prepareAuthorizationHeader();

    return $options;
  }


  /**
   * Set the options (headers, data..) for a POST request using CURL
   *  
   * @param array  $data         
   *   The data with the info for the POST request  
   * @param string $endpoint     
   *   The endpoint of the API collection we will call  
   *
   * @return array               
   *   The array with all the options
   */
  public function setCurlPostOptions($data, $endpoint = ''){
    
    //This API wants the data in JSON format
    if (is_array($data)){
      $data = json_encode($data);  
    }
    //If we dont specify the endpoint, we use the local one of the object (We use this function for all the requests)
    $endpoint = ($endpoint == '' ? $this->_endpoint : $endpoint);
    //Set all the options
    $options[CURLOPT_URL]            = $this->getApiUrl().$endpoint;
    $options[CURLOPT_RETURNTRANSFER] = true;
    $options[CURLOPT_POST]           = true; 
    $options[CURLOPT_POSTFIELDS]     = $data; //The data for the POST request
    if ($this->_access_token == ''){
      //No token, header for request a new one
      $options[CURLOPT_HTTPHEADER]   = array('Accept: application/json', 'Content-Type: application/json');
    } else {
      //Header with token authorization
      $options[CURLOPT_HTTPHEADER]   = $this->prepareAuthorizationHeader();  
    }
    //Return the array with all the options for the headers
    return $options;
  }


  /**
   * Set an special header for the CURL function with the authorization for the API  
   *
   * @return array              
   *   The array the authorization header
   */
  private function prepareAuthorizationHeader(){
    //We set this specific header
    return array('Authorization: Bearer '.$this->getApiToken(), 'Content-Type: application/json');
  }

}