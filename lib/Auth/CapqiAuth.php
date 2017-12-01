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
 * CAPQI Auth Main class
 */
class CapqiAuth{

  /**
   * Default url where the API is located. User can overwrite it via the settings parameter when initializing the object
   *
   * @var string
   */
  private $baseUrl = 'https://transparencyatwork.org/api/partners/v1/';


  /**
   * Get an Auth collection object
   *
   * @param array   $settings 
   *   Settings with the neccesary info for the Auth connection   
   * @param string  $autMethod 
   *   Name of the class we are going to use for the connection
   *   
   * @throws Exception
   * @return Auth\Class
   *   An object of the class we have called
   */
  public function newAuth($settings, $authMethod = 'BasicAuth'){

    $class = "Capqi\\Auth\\".$authMethod;

    if (!class_exists($class)) {
      //Todo: differenciate exceptions??
      throw new Exception("A context of ".$apiCollection." was not found.");
    }

    //The class exists, we declare it
    $authObject = new $class();
    //We use the ReflectionMethod to load a function inside a class
    $reflection = new \ReflectionMethod($class, 'initialize');
    $args       = array();

    //We loop the arguments the function we have used ('initialize') has
    foreach ($reflection->getParameters() as $param) {
      //The url is special, because we have a default one in this class
      if($param->getName() === 'url'){
        if (isset($settings['url']) && $settings['url'] != '') {
        //We add it to the arguments we will use to invoke our object
          $args[] = $settings['url'];
        } else {
          $args[] = $this->baseUrl;
        }
      } else {
        if (isset($settings[$param->getName()])) {
          //We add it to the arguments we will use to invoke our object
          $args[] = $settings[$param->getName()];
        } else {
          $args[] = null;
        } 
      }
    }
    //invoke the function with the parameters for the arguments and return the object created by this operation
    $reflection->invokeArgs($authObject, $args);
    return $authObject;
  }

}