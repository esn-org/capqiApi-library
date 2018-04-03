<?php

/**
 * @package     CAPQI
 * @copyright   2017. Erasmus Student Network AISBL.
 * @author      Gorka Guerrero <web-developer@esn.org>
 * @link        http://esn.org
 * @license     
 */

namespace Capqi\Api;


/**
 * Sectors Collection
 */
class Sectors extends Api{

  /**
   * {@inheritdoc}
   */
  protected $endpoint = 'sectors';
  protected $pathUrl  = '/sectors/';

  /**
   * {@inheritdoc}
   */
  protected $allowedSearchParameters = [];


  /**
   * Does the API request to get all the items of this collection
   *
   * @return array
   *   Array with the response from the API request
   */
  public function get(){

    return $this->genericFullGet();
  }


}