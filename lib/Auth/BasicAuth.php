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
   * Default lang for the API url. Depending on the lang, some values will be translated
   *
   * @var string
   */
  protected $_lang;

  /**
   * Token is returned if the Authetication is success
   *
   * @var string
   */
  protected $_access_token;

  /**
   * PartnerID is returned if the Authetication is success
   *
   * @var string
   */
  protected $_partner_id;

  /**
   * New Review URL is returned if the Authetication is success
   *
   * @var string
   */
  protected $_new_review_url;

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
  public function initialize($email, $password, $lang = 'en', $url, $access_token = null) {
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
    $this->_lang     = $lang;

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
      //We got the new access_token and other info from the response if success
      $this->_access_token   = $body['access_token'];
      $this->_partner_id     = $body['id'];
      $this->_new_review_url = $body['reviews_url'];

      $this->setHttpResponseMesagge('Logged successfuly using mail/pass via API');
    } else if (isset($body['status']) && $body['status'] == '404') {
      $this->setHttpCode(404);
      $this->setHttpResponseMesagge($body['error']);
    } else {
      //We got an error (and we store it)
      $this->setHttpResponseMesagge($body['errors']);
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
   * Getter. Gets the API lang.
   *
   * @return string 
   *   The API lang.
   */
  public function getApiLang(){
    return $this->_lang;
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
   * Getter. Gets the API PartnerID linked to the logged user.
   *
   * @return string
   *   The API PartnerID linked to the logged user.
   */
  public function getApiPartnerId(){
    return $this->_partner_id;
  }


  /**
   * Getter. Gets the API URL for writting new reviews linked to the logged user.
   *
   * @return string
   *   The API URL for writting new reviews linked to the logged user.
   */
  public function getApiNewReviewUrl(){
    return $this->_new_review_url;
  }


}