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
   * Does the API request to get all the items of this collection
   *
   * @return array
   *   Array with the response from the API request
   */
  public function getList(){

    return $this->getFullList();
  }


  /**
   * Does the API request to get an item of this collection
   *
   * @param int   $id
   *   The id of the item (employer) we want to get from the API
   *
   * @return array
   *   Array with the response from the API request
   */
  public function get($id = ''){

    return $this->genericGet($id);
  }


  /**
   * Does the API request to get an item of this collection
   *
   * @param string  $name
   *   The name of the employer to create
   * @param int     $sector
   *   The sector where the employer operates on (from the sector list)
   * @param string  $country
   *   The country code (2-digits ISO format) of the country where the employer is from
   *
   * @return array
   *   Array with the response from the API request
   */
  public function put($name, $sector, $country){

    return $this->genericPost($name, $sector, $country);
  }


  /**
   * Does the API request to get all the items on a page (PAGE_ITEMS) of this collection
   *
   * @param int  $page
   *   The page we want to get the list of employers from. Default value = 1
   * @param int  $items
   *   The number of items (employers) we want to get. Default value = PAGE_ITEMS
   *
   * @return array
   *   Array with the response from the API request
   */
  public function getListPage($page = 1, $items = PAGE_ITEMS){

    return $this->getSinglePageList($page, $items);
  }


  /**
   * Creates and returns the URL to the employer profile in the website
   * @deprecated Added profile_url in response
   *
   * @param string $user
   *   The user name (tw_id) of the employer
   *
   * @return string
   *   URL to the employer profile in the website
   *
   * @deprecated
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
   *
   * @deprecated
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