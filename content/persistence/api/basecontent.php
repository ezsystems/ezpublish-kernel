<?php
/**
 * File containing BaseContent class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license //EZP_LICENCE//
 * @version //autogentag//
 * @package ezpublish
 * @subpackage persistence
 */
namespace ezp\Content\Persistence\API;

/**
 * Base class for content
 * To be extended by any content storage engine
 */
abstract class BaseContent
{
    protected $properties = array();

    public function __get( $name )
    {

    }

    public function __set( $name, $value )
    {

    }
}
?>
