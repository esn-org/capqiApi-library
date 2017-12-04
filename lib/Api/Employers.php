<?php

/**
 * @package     CAPQI
 * @copyright   2017. Erasmus Student Network AISBL.
 * @author      Gorka Guerrero <web-developer@esn.org>
 * @link        http://esn.org
 * @license     
 */

namespace Capqi\Api;

use Capqi\Functions\Urls;


/**
 * Employers Collection
 */
class Employers extends Api{

  /**
   * {@inheritdoc}
   */
  protected $endpoint = 'employers';
  protected $pathUrl  = '/employers/';

  /**
   * {@inheritdoc}
   */
  protected $allowedSearchParameters = ['employer_name', 'country_code'];

  /**
   * Does the API request to search for all the items of this collection
   *
   * @return array
   *   Array with the response from the API request
   */
  public function getList(){

    return $this->genericGetList();
  }

  /**
   * Does the API request to search for all the items of this collection
   *
   * @return array
   *   Array with the response from the API request
   */
  public function get($id){

    return $this->genericGet($id);
  }


  /**
   * Creates and returns the URL to the employer profile in the website
   *
   * @param string $user
   *   The user name (tw_id) of the employer
   *
   * @return string
   *   URL to the employer profile in the website
   */
  public function createProfileLink($user){

    $url = $this->createUrlLink();
    return $url.$user;
    
  }


  /**
   * Creates and returns the URL to the website (without the user endpoint)
   *
   * @return string
   *   URL to the website without the user endpoint
   */
  public function createUrlLink(){
    $host = $this->auth->getApiHostUrlonly();
    $path = $this->pathUrl;

    //We dont want double slashes in the url or no slashes, so we check all the posibilities
    if ((Urls::endsWith($host, '/')) && (Urls::startsWith($path, '/'))){
      //That is the double case, we remove one of the slashes (from host url)
      $host = Urls::removeLastSlash($host);
      $url = $host.$path; 

    } else if ((Urls::endsWith($host, '/')) || (Urls::startsWith($path, '/'))) {
      //Slash in one of both sides, is fine
      $url = $host.$path;

    } else {
      //No slashes at all
      $url = $host.'/'.$path;  
    }
    
    //Also we check the final url and add one if necessary
    if (Urls::endsWith($url,'/')){
      return $url;
    } else {
      return $url.'/';
    }
  }

}