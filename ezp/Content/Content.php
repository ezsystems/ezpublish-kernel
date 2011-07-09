<?php
/**
 * File containing the ezp\Content\Content class.
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
 *                The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property-read Version[] $versions
 *                   Iterable collection of versions for content. Array-accessible :;
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
 *                                          Collection of ezp\Content objects, related to the current one
 * @property Content[] $reverseRelations
 *                                          Collection of ezp\Content objects, reverse-related to the current one
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
namespace ezp\Content;
class Content extends \ezp\Base\AbstractModel
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
        'versions' => false,
    );

    /**
     * Create content based on content type object
     *
     * @param type\Type $contentType
     * @param Locale $mainLocale
     */
    public function __construct( type\Type $contentType, \ezp\Base\Locale $mainLocale )
    {
        $this->creationDate = new \DateTime();
        $this->mainLocale = $mainLocale;
        $this->alwaysAvailable = false;
        $this->locations = new \ezp\Base\TypeCollection( '\ezp\Content\Location' );
        $this->relations = new \ezp\Base\TypeCollection( '\ezp\Content\Content' );
        $this->reversedRelations = new \ezp\Base\TypeCollection( '\ezp\Content\Content' );
        $this->translations = new \ezp\Base\TypeCollection( '\ezp\Content\Translation' );
        $this->name = false;
        $this->contentType = $contentType;
        $this->addTranslation( $mainLocale );
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
     * The Content's status, as one of the ezp\Content::STATUS_* constants
     *
     * @var int
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * Locations collection
     *
     * @var Location[]
     */
    protected $locations;

    /**
     * Content type object that this Content object is an instance of
     *
     * @var type\Type
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
     * Always available flag
     *
     * @var boolean
     */
    public $alwaysAvailable;

    /**
     * Locale
     *
     * @var Locale
     */
    protected $mainLocale;

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
     * Return a collection containing all available versions of the Content
     * 
     * @return Version[]
     */
    protected function getVersions()
    {
        $resultArray = array();
        foreach( $this->translations as $tr )
        {
            $versionsArray = $tr->versions->getArrayCopy();
            $resultArray = array_merge( $resultArray, $versionsArray );
        }
        return new \ezp\Base\TypeCollection( '\ezp\Content\Version', $resultArray );
    }

    /**
     * Find current version amongst version objects
     *
     * @return Version|null
     */
    protected function getCurrentVersion()
    {
        foreach ( $this->versions as $contentVersion )
        {
            if ( $this->currentVersion == $contentVersion->version )
                return $contentVersion;
        }
        return null;
    }

    /**
     * Return type\Type object
     *
     * @return type\Type
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
     * Adds a Translation in $locale optionally based on existing
     * translation in $base.
     *
     * @param \ezp\Base\Locale $locale
     * @param \ezp\Content\Version $base
     * @return Translation
     * @throw \InvalidArgumentException if translation in $base does not exist.
     */
    public function addTranslation( \ezp\Base\Locale $locale, \ezp\Base\Version $base = null )
    {
        if ( isset( $this->translations[$locale->code] ) )
        {
            throw new \InvalidArgumentException( "Translation {$locale->code} already exists" );
        }

        $tr = new Translation( $locale, $this );
        $this->translations[$locale->code] = $tr;

        $newVersion = null;
        if ( $base !== null )
        {
            $newVersion = clone $base;
            $newVersion->locale = $locale;
        }
        if ( $newVersion === null )
        {
            $newVersion = new Version( $this, $locale );
        }
        $tr->versions[] = $newVersion;
        return $tr;
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
        $this->locations = new \ezp\Base\TypeCollection( '\ezp\Content\Location' );
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
