<?php
/**
 * File containing the ezp\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Content\Translation,
    ezp\Content\Type,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Base\Proxy,
    ezp\Content\Version,
    ezp\Content\Version\StaticCollection as VersionCollection,
    ezp\Persistence\Content as ContentValue,
    DateTime,
    InvalidArgumentException;

/**
 * This class represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read mixed $id The Content's ID, automatically assigned by the persistence layer
 * @property-read int $currentVersionNo The Content's current version
 * @property-read int $status The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property string[] $name The Content's name
 * @property-read mixed $ownerId Id of the user object that owns the content
 * @property-read bool $alwaysAvailable The Content's always available flag
 * @property-read string $remoteId The Content's remote identifier (custom identifier for the object)
 * @property-read mixed $sectionId Read property for section id, use with object $section to change
 * @property-read mixed $typeId Read property for type id
 * @property-read \ezp\Content\Type $contentType The Content's type
 * @property-read \ezp\Content\Version[] $versions
 *                Iterable collection of versions for content. Array-accessible :;
 *                <code>
 *                $myFirstVersion = $content->versions[1];
 *                $myThirdVersion = $content->versions[3];
 *                </code>
 * @property-read \ezp\Content\Version $currentVersion Current version of content
 * @property-read \ezp\Content\Location[] $locations
 *                Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                First location referenced in the collection represents the main location for content
 *                <code>
 *                $mainLocation = $content->locations[0];
 *                $anotherLocation = $content->locations[2];
 *                $locationById = $content->locations->byId( 60 );
 *                </code>
 * @property-read DateTime $creationDate The date the object was created
 * @property \ezp\Content\Section $section The Section the content belongs to
 * @property \ezp\Content\Relation[] $relations Collection of \ezp\Content\Relation objects, related to the current one
 * @property \ezp\Content\Relation[] $reverseRelations Collection of \ezp\Content\Relation objects, reverse-related to the current one
 * @property \ezp\Content\Translation[] $translations
 *           Collection of content's translations, indexed by locale (ie. eng-GB)
 *           <code>
 *           $myEnglishTranslation = $content->translations["eng-GB"];
 *           $myEnglishTitle = $content->translations["eng-GB"]->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property \ezp\Content\Field[] $fields
 *           Collection of content's fields in default (current) language.
 *           Shorthand property to directly access to the content's fields in current language
 *           <code>
 *           $myTitle = $content->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property int $ownerId Owner identifier
 */
class Content extends Model
{
    /**
     * Publication status constants
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'currentVersionNo' => false,
        'status' => false,
        'name' => true, // @todo: Make readOnly and generate on store event from attributes based on type nameScheme
        'ownerId' => true,// @todo make read only by providing interface that takes User as input
        'alwaysAvailable' => true,
        'remoteId' => true,// @todo Make readonly and deal with this internally (in all DO's)
        'sectionId' => false,
        'typeId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'creationDate' => false,
        'mainLocation' => false,
        'section' => false,
        'fields' => true,
        'contentType' => false,
        'versions' => false,
        'locations' => true,
        //'translations' => true,
        'relations' => false,
        'reversedRelations' => false,
        'currentVersion' => false
    );

    /**
     * The Section the content belongs to
     *
     * @var \ezp\Content\Section
     */
    protected $section;

    /**
     * The Content's status, as one of the ezp\Content::STATUS_* constants
     * @todo Move to VO!
     *
     * @var int
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * Locations collection
     *
     * @var \ezp\Content\Location[]
     */
    protected $locations;

    /**
     * Content type object that this Content object is an instance of
     *
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * Relations collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $reversedRelations;

    /**
     * Versions
     *
     * @var \ezp\Content\Version[]
     */
    protected $versions;

    /**
     * Create content based on content type object
     *
     * @param \ezp\Content\Type $contentType
     */
    public function __construct( Type $contentType )
    {
        $this->properties = new ContentValue( array( 'typeId' => $contentType->id ) );
        /*
        @TODO Make sure all dynamic properties writes to value object if scalar value (creationDate (int)-> properties->created )
        */
        $this->contentType = $contentType;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        $this->relations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->reversedRelations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->versions = new VersionCollection( array( new Version( $this ) ) );
    }

    /**
     * Return Main location object on this Content object
     *
     * @return \ezp\Content\Location
     */
    protected function getMainLocation()
    {
        return $this->locations[0];
    }

    /**
     * Return a collection containing all available versions of the Content
     *
     * @return \ezp\Content\Version[]
     */
    protected function getVersions()
    {
        return $this->versions;
    }

    /**
     * Find current version amongst version objects
     *
     * @return \ezp\Content\Version|null
     */
    protected function getCurrentVersion()
    {
        foreach ( $this->versions as $contentVersion )
        {
            if ( $this->properties->currentVersionNo == $contentVersion->versionNo )
                return $contentVersion;
        }
        return null;
    }

    /**
     * Return Type object
     *
     * @return \ezp\Content\Type
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
     * @todo Do we really want/need this shortcut?
     *
     * @return \ezp\Content\Field[]
     */
    protected function getFields()
    {
        return $this->getCurrentVersion()->fields;
    }

    /**
     * Sets the Section the Content belongs to
     *
     * @param \ezp\Content\Section $section
     */
    protected function setSection( Section $section )
    {
        $this->section = $section;
        $this->properties->sectionId = $section->id;
    }

    /**
     * Returns the Section the Content belongs to
     *
     * @return \ezp\Content\Section
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
     * Adds a new location to content under an existing one.
     *
     * @param \ezp\Content\Location $parentLocation
     * @return \ezp\Content\Location
     */
    public function addParent( Location $parentLocation )
    {
        $newLocation = new Location( $this );
        $newLocation->parent = $parentLocation;
        return $newLocation;
    }

    /**
     * Gets locations
     *
     * @return \ezp\Content\Location[]
     */
    protected function getLocations()
    {
        return $this->locations;
    }

    /**
     * Gets Content relations
     *
     * @return \ezp\Content[]
     */
    protected function getRelations()
    {
        return $this->relations;
    }

    /**
     * Gets Content reverse relations
     *
     * @return \ezp\Content[]
     */
    protected function getReverseRelations()
    {
        return $this->reverseRelations;
    }

    /**
     * Clone content object
     */
    public function __clone()
    {
        $this->properties->id = false;
        $this->status = self::STATUS_DRAFT;

        // Get the location's, so that new content will be the old one's sibling
        $oldLocations = $this->locations;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        foreach ( $oldLocations as $location )
        {
            $this->addParent( $location->parent );
        }
    }
}
?>
