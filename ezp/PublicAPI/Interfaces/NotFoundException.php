<?php
namespace ezp\PublicAPI\Interfaces\Exception;

use ezp\PublicAPI\Interfaces\Exception;

/**
 * This Exception is thrown if an object referencenced by an id or identifier 
 * could not be found in the repository. 
 * @package ezp\PublicAPI\Interfaces
 *
 */
abstract class NotFoundException extends RuntimeException implements Exception {
    
}

