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
 * @property-read integer status
 *                The Content's status, as one of the ezp\Content::STATUS_* constants
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

use ezx\doctrine\model\ContentType;

use ezp\User\Repository as UserRepository;
use ezp\User\User;

class Content extends Base implements \ezp\DomainObjectInterface
{
    /**
     * Publication status constants
     * @var integer
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * A custom ID for the object
     *
     * @var string
     */
    public $remoteId = "";

    /**
     * The date the object was created
     *
     * @var DateTime
     */
    public $creationDate;

    /**
     * The id of the user who first created the content
     *
     * @var int
     */
    public $ownerId = 0;

    /**
     * The Section the content belongs to
     *
     * @var Proxy|Section
     */
    public $section = 0;

    /**
     * The Content's status, as one of the ezp\Content::STATUS_* constants
     *
     * @var int
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * Versions collection
     *
     * @var VersionCollection
     */
    protected $versions;

    /**
     * Locations collection
     *
     * @var LocationCollection
     */
    protected $locations;

    /**
     * Relations collection
     *
     * @var RelationCollection
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var RelationCollection
     */
    protected $reversedRelations;

    /**
     * Translations collection
     *
     * @var TranslationCollection
     */
    protected $translations;

    /**
     * Fields collection
     *
     * @var FieldCollection
     */
    protected $fields;

    /**
     * Name of the content
     *
     * @var string
     */
    protected $name;

    public function __construct( ContentType $contentType = null )
    {
        $this->creationDate = new \DateTime();

        $this->versions = new VersionCollection();
        $this->locations = new LocationCollection();
        $this->relations = new RelationCollection();
        $this->reversedRelations = new RelationCollection();
        $this->translations = new TranslationCollection();
        $this->fields = new FieldCollection();
        $this->name = false;

        $this->readableProperties = array(
            "id"                    => true,
            "status"                => true,
            "versions"              => true,
            "locations"             => true,
            "relations"             => true,
            "reversedRelations"     => true,
            "translations"          => true,
            "fields"                => true,
            "name"					=> true,
        );

        $this->dynamicProperties = array(
            "mainLocation" => true,
            "section" => true,
            "sectionId" => true,
        );
    }

    protected function getMainLocation()
    {
        return $this->locations[0];
    }

    /**
     * Sets the Section the Content belongs to
     *
     * @param Proxy|Section $section
     * @throws \InvalidArgumentException if $content is not an instance of the
     *                                   right class
     */
    protected function setSection( $section )
    {
        if ( !$section instanceof Section && !$section instanceof Proxy )
        {
            throw new \InvalidArgumentException( "Parameter needs to be an instance of Location or Section class" );
        }
        else
        {
            $this->section = $section;
        }
    }

    /**
     * Returns the Section the Content belongs to
     *
     * @return Section
     */
    protected function getSection()
    {
        if ( $this->section instanceof Proxy )
        {
            $this->section = $this->section->load();
        }
        return $this->section;
    }

    /**
     * Returns the section's id
     *
     * @return int
     */
    protected function getSectionId()
    {
        if  ( $this->section instanceof Base )
        {
            return $this->section->id;
        }
        return 0;
    }

    /**
     * Adds a new translation for content, referenced by $localeCode
     * @param string $localeCode
     */
    public function addTranslation( $localeCode )
    {
        $this->translations[] = new Translation( $localeCode );
    }

    /**
     * Adds a new location to content under an existing one.
     *
     * @param Location $parentLocation
     * @return Location
     */
    public function addLocationUnder( Location $parentLocation )
    {
        $location = new Location();
        $location->parent = $parentLocation;
        $this->locations[] = $location;
        return $location;
    }

    public function __clone()
    {
        $this->id = false;
        $this->status = self::STATUS_DRAFT;

        // Locations : get the main location's parent, so that new content will 
        // be the old one's sibling
        $parentLocation = $this->locations[0]->parent;
        $this->locations = new LocationCollection();
        $this->addLocationUnder( $parentLocation );

        $this->ownerId = UserRepository::get()->currentUser()->id;

        // Detach data from persistence
        $this->versions->detach();
        $this->relations->detach();
        $this->reversedRelations->detach();
        $this->translations->detach();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Free some in-memory cache here
    }

    public function __toString()
    {
        return $this->name;
    }
}
?>
