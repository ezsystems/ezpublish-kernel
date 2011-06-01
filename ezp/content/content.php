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
 *                The Content's ID, automatically assigned by the persistence layer
 * @property DateTime $creationDate
 *           The date the object was created
 * @property-read integer status
 *                The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property string $remoteId
 *           A custom ID for the object. It receives a default value, but can be changed to anything
 * @property-read ezp\Content\VersionCollection $versions
 *                   Iterable collection of versions for content, indexed by version number. Array-accessible :
 *                   <code>
 *                   $myFirstVersion = $content->versions[1];
 *                   $myThirdVersion = $content->versions[3];
 *                   </code>
 * @property-read ezp\Content\LocationCollection $locations
 *                   Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                   First location referenced in the collection represents the main location for content
 *                   <code>
 *                   $mainLocation = $content->locations[0];
 *                   $anotherLocation = $content->locations[2];
 *                   $locationById = $content->locations->byId( 60 );
 *                   </code>
 * @property ezp\User $owner
 *                       User that first created the content
 * @property ezp\Content\RelationCollection $relations
 *                                          Collection of ezp\Content objects, related to the current one
 * @property ezp\Content\RelationCollection $reverseRelations
 *                                          Collection of ezp\Content objects, reverse-related to the current one
 * @property ezp\Content\TranslationCollection $translations
 *                                             Collection of content's translations, indexed by locale (ie. eng-GB)
 *                                             <code>
 *                                             $myEnglishTranslation = $content->translations["eng-GB"];
 *                                             $myEnglishTitle = $content->translations["eng-GB"]->fields->title; // Where "title" is the field identifier
 *                                             </code>
 * @property ezp\Content\FieldCollection $fields
 *                                       Collection of content's fields in default (current) language.
 *                                       Shorthand property to directly access to the content's fields in current language
 *                                       <code>
 *                                       $myTitle = $content->fields->title; // Where "title" is the field identifier
 *                                       </code>
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

use ezp\User\UserRepository;
use ezp\User\User;

class Content extends Base implements DomainObjectInterface
{
    /**
     * Publication status constants
     * @var integer
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    public function __construct( ContentType $contentType )
    {
        $this->properties = array(
            "id"                    => null,
            "remoteId"              => null,
            "status"                => self::STATUS_DRAFT,
            "versions"              => new VersionCollection(),
            "locations"             => new LocationCollection(),
            "creationDate"          => new DateTime(),
            "owner"                 => UserRepository::get()->currentUser(),
            "relations"             => new RelationCollection(),
            "reversedRelations"     => new RelationCollection(),
            "translations"          => new TranslationCollection(),
            "fields"                => new FieldsetCollection()
        );

        $this->readOnlyProperties = array(
            "id"            => true,
            "status"        => true,
            "versions"      => true,
            "locations"     => true
        );
    }

    /**
     * Restores the state of a content object
     * @param array $state
     */
    public static function __set_state( array $state )
    {
        $obj = new self;
        foreach ( $state as $property => $value )
        {
            if ( isset( $obj->properties[$property] ) )
            {
                $obj->properties[$property] = $value;
            }
        }

        return $obj;
    }

    public function __clone()
    {
        $this->properties["id"] = null;
        $this->properties["status"] = self::STATUS_DRAFT;

        // Locations : get the current location's parent, so that new content will be the old one's sibling
        $parentLocation = $this->properties["locations"]->current->parent;
        $this->properties["locations"] = new LocationCollection();
        $this->properties["locations"]->add( $parentLocation );

        $this->properties["owner"] = UserRepository::get()->currentUser();

        // Detach data from persistence
        $this->properties["versions"]->detach();
        $this->properties["relations"]->detach();
        $this->properties["reversedRelations"]->detach();
        $this->properties["translations"]->detach();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Free some in-memory cache here
    }
}
?>