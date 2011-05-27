<?php
/**
 * File containing the ezp\Content\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read integer $id
 *                The Content's ID, automaticaly assigned by the persistence layer
 * @property eZDateTime $creationDate
 *           The date the object was created
 * @property-read integer status
 *                The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property string $remoteId
 *           A custom ID for the object. It receives a default value, but can be changed to anything
 * @property-read ezp\Content\VersionCollection versions
 * @property-read ezp\Content\TranslationCollection locations
 * @property ezp\User $owner
 * @property ezp\Content\RelationCollection $relations
 * @property ezp\Content\RelationCollection $reverseRelations
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class Content
{
    public function __get( $property )
    {
        throw new ezcBasePropertyNotFoundException( $property );
    }

    public function __isset( $property )
    {
        throw new ezcBasePropertyNotFoundException( $property );
    }

    public function __set( $property, $value )
    {
        throw new ezcBasePropertyNotFoundException( $property );
    }

    public function __set_state( $array )
    {

    }
}
?>