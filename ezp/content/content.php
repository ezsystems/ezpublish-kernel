<?php
/**
 * File containing the ezp\content\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read integer $id
 *                The Content's ID, automatically assigned by the persistence layer
 * @property-read integer status
 *                The Content's status, as one of the ezp\content::STATUS_* constants
 * @property-read Version[] $versions
 *                   Iterable collection of versions for content, indexed by version number. Array-accessible :
 *                   <code>
 *                   $myFirstVersion = $content->versions[1];
 *                   $myThirdVersion = $content->versions[3];
 *                   </code>
 * @property-read Location[] $locations
 *                   Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                   First location referenced in the collection represents the main location for content
 *                   <code>
 *                   $mainLocation = $content->locations[0];
 *                   $anotherLocation = $content->locations[2];
 *                   $locationById = $content->locations->byId( 60 );
 *                   </code>
 * @property Content[] $relations
 *                                          Collection of ezp\content objects, related to the current one
 * @property Content[] $reverseRelations
 *                                          Collection of ezp\content objects, reverse-related to the current one
 * @property Translation[] $translations
 *                                             Collection of content's translations, indexed by locale (ie. eng-GB)
 *                                             <code>
 *                                             $myEnglishTranslation = $content->translations["eng-GB"];
 *                                             $myEnglishTitle = $content->translations["eng-GB"]->fields->title; // Where "title" is the field identifier
 *                                             </code>
 * @property Field[] $fields
 *                                       Collection of content's fields in default (current) language.
 *                                       Shorthand property to directly access to the content's fields in current language
 *                                       <code>
 *                                       $myTitle = $content->fields->title; // Where "title" is the field identifier
 *                                       </code>
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Content extends \ezp\base\AbstractModel
{
    /**
     * Publication status constants
     * @var integer
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'currentVersion' => false,
        'status' => false,
        'name' => false,
        'ownerId' => true,
        'relations' => false,
        'reversedRelations' => false,
        'translations' => true,
        'locations' => true,
        'contentType' => false,
        'versions' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'mainLocation' => false,
        'section' => false,
        'sectionId' => false,
        'fields' => true,
        'contentType' => false,
    );

    /**
     * Create content based on content type object
     *
     * @param ContentType $contentType
     */
    public function __construct( ContentType $contentType )
    {
        $this->creationDate = new \DateTime();
        $this->versions = new \ezp\base\TypeCollection( '\ezp\content\Version' );
        $this->locations = new \ezp\base\TypeCollection( '\ezp\content\Location' );
        $this->relations = new \ezp\base\TypeCollection( '\ezp\content\Content' );
        $this->reversedRelations = new \ezp\base\TypeCollection( '\ezp\content\Content' );
        $this->translations = new \ezp\base\TypeCollection( '\ezp\content\Translation' );
        $this->name = false;
        $this->typeId = $contentType->id;
        $this->contentType = $contentType;
    }

    /**
     * Content object id.
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Content object current version.
     *
     * @var int
     */
    protected $currentVersion = 0;

    /**
     * A custom ID for the object
     *
     * @var string
     */
    public $remoteId = '';

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
     * @var Section
     */
    public $section;

    /**
     * The Content's status, as one of the ezp\content::STATUS_* constants
     *
     * @var int
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * Collection of content versions.
     *
     * @var Version[]
     */
    protected $versions;

    /**
     * Locations collection
     *
     * @var Location[]
     */
    protected $locations;

    /**
     * Content type object that this Content object is an instance of
     *
     * @var ContentType
     */
    protected $contentType;

    /**
     * Relations collection
     *
     * @var Content[]
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var Content[]
     */
    protected $reversedRelations;

    /**
     * Translations collection
     *
     * @var Translation[]
     */
    protected $translations;

    /**
     * Name of the content
     *
     * @var string
     */
    protected $name;

    /**
     * Return Main location object on this Content object
     *
     * @return Location
     */
    protected function getMainLocation()
    {
        return $this->locations[0];
    }

    /**
     * Find current version amongst version objects
     *
     * @return Version|null
     */
    protected function getCurrentVersion()
    {
        foreach( $this->getVersions() as $contentVersion )
        {
            if ( $this->currentVersion == $contentVersion->version )
                return $contentVersion;
        }
        return null;
    }

    /**
     * Return ContentType object
     *
     * @return ContentType
     */
    protected function getContentType()
    {
        if ( $this->contentType instanceof Proxy )
        {
            return $this->contentType = $this->contentType->load();
        }
        return $this->contentType;
    }

    /**
     * Get fields of current version
     *
     * @return Field[]
     */
    protected function getFields()
    {
        return $this->getCurrentVersion()->fields;
    }

    /**
     * Sets the Section the Content belongs to
     *
     * @param Section $section
     */
    protected function setSection( Section $section )
    {
        $this->section = $section;
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
        if  ( $this->section instanceof Proxy || $this->section instanceof Section )
        {
            return $this->section->id;
        }
        return 0;
    }

    /**
     * Adds a new translation for content, referenced by $localeCode
     * @param Locale $locale
     * @return Translation
     */
    public function addTranslation( Locale $locale )
    {
        $this->translations[$locale->code] = new Translation( $locale, $this );
        return $this->translations[$locale->code];
    }

    /**
     * Adds a new location to content under an existing one.
     *
     * @param Location $parentLocation
     * @return Location
     */
    public function addParent( Location $parentLocation )
    {
        $newLocation = new Location( $this );
        $newLocation->parent = $parentLocation;
        return $newLocation;
    }

    /**
     * Clone content object
     */
    public function __clone()
    {
        $this->id = false;
        $this->status = self::STATUS_DRAFT;

        // Get the location's, so that new content will be the old one's sibling
        $oldLocations = $this->locations;
        $this->locations = new \ezp\base\TypeCollection( '\ezp\content\Location' );
        foreach ( $oldLocations as $location )
        {
            $this->addParent( $location->parent );
        }

        // Detach data from persistence
        $this->versions->detach();
        $this->relations->detach();
        $this->reversedRelations->detach();
        $this->translations->detach();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
?>
