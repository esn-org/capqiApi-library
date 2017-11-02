<?php

/**
 * @package     CAPQI
 * @copyright   2017. Erasmus Student Network AISBL.
 * @author      Gorka Guerrero <web-developer@esn.org>
 * @link        http://esn.org
 * @license     
 */

namespace Capqi;

use \Exception;

//TODO: use interface if we set the type in the parameters???

/**
 * CAPQI API Factory
 */
class CapqiApi{


  /**
   * Get an API collection object
   *
   * @param string  $apiCollection 
   *   Name of the class we are going to use (employers, etc..)
   * @param object  $auth 
   *   The Auth object with the info for the API calls and CURL methods
   *
   * @return Api\Class
   *   An object of the class we have called
   * @throws Exception
   */
  //TODO: make auth an interface if we can have more than one type of auth???
  public function newCollection($apiCollection, $auth){
    //Make sure the collection name us UC first and declare it
    $apiCollection = ucfirst($apiCollection);
    $class = "Capqi\\Api\\".$apiCollection;

    if (!class_exists($class)) {
      //Todo: differenciate exceptions??
      throw new Exception("A context of ".$apiCollection." was not found.");
    }
    //Class exists, initialize it with the auth object as parameter
    return new $class($auth); 
  }

}