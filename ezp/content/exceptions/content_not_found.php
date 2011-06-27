<?php
/**
 * File containing ezp\content\ContentNotFoundException class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class ContentNotFoundException extends \ezp\base\Exception
{
    /**
     * Constructs a new ezp\base\Exception with $message
     *
     * @param mixed $id
     * @param string $class ezp\content class that where not found
     * @param string $property The property that where matched against
     */
    public function __construct( $id, $class = 'Content', $property = 'id' )
    {
        parent::__construct( "Could not find $class with $property: {$id}" );
    }
}
?>
