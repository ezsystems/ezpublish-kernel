<?php
namespace ezp\PublicAPI\Interfaces\Exception;

use ezp\PublicAPI\Interfaces\Exception;
/**
 * This Exception is thrown if the current user is not allowed to perform an action in the reposittory. 
 *
 * @package ezp\PublicAPI\Interfaces
 */
abstract class Unauthorized extends RuntimeException implements Exception {
    
}