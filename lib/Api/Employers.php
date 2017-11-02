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
 * Employers Collection
 */
class Employers extends Api{

  /**
   * {@inheritdoc}
   */
  protected $endpoint = 'employers';

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

    //This is a special type of get with no $id as argument
    return $this->get('');
  }

}