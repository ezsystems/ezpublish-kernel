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
    ezp\Base\Observable,
    ezp\Base\Locale,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Content\Translation,
    ezp\Content\Type,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Content\Proxy,
    ezp\Content\Version,
    ezp\Persistence\Content as ContentValue,
    DateTime,
    InvalidArgumentException;

/**
 * This class represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read int $id The Content's ID, automatically assigned by the persistence layer
 * @property-read int $currentVersion The Content's current version
 * @property-read string $remoteId The Content's remote identifier (custom identifier for the object)
 * @property string $name The Content's name
 * @property-read bool $alwaysAvailable The Content's always available flag
 * @property-read int status The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property-read \ezp\Content\Type contentType The Content's type
 * @property-read \ezp\Content\Version[] $versions
 *                Iterable collection of versions for content. Array-accessible :;
 *                <code>
 *                $myFirstVersion = $content->versions[1];
 *                $myThirdVersion = $content->versions[3];
 *                </code>
 * @property-read \ezp\Content\Location[] $locations
 *                Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                First location referenced in the collection represents the main location for content
 *                <code>
 *                $mainLocation = $content->locations[0];
 *                $anotherLocation = $content->locations[2];
 *                $locationById = $content->locations->byId( 60 );
 *                </code>
 * @property-read DateTime $creationDate The date the object was created
 * @property-read \ezp\Content\Section $section The Section the content belongs to
 * @property \ezp\Content[] $relations Collection of ezp\Content objects, related to the current one
 * @property \ezp\Content[] $reverseRelations Collection of ezp\Content objects, reverse-related to the current one
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
        'currentVersion' => false,
        'status' => false,
        'name' => true, // @todo: Make readOnly and generate on store event from attributes based on type nameScheme
        'ownerId' => true,
        'relations' => false,
        'reversedRelations' => false,
        'translations' => true,
        'locations' => true,
        'alwaysAvailable' => true,
        'remoteId' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'creationDate' => false,
        'mainLocation' => false,
        'section' => false,
        'sectionId' => false,
        'fields' => true,
        'contentType' => false,
        'versions' => false,
    );

    /**
     * The Section the content belongs to
     *
     * @var \ezp\Content\Section
     */
    protected $section;

    /**
     * The Content's status, as one of the ezp\Content::STATUS_* constants
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
     * @var \ezp\Content[]
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var \ezp\Content[]
     */
    protected $reversedRelations;

    /**
     * Translations collection
     *
     * @var \ezp\Content\Translation[]
     */
    protected $translations;

    /**
     * \ezp\Base\Locale
     *
     * @var \ezp\Base\Locale
     */
    protected $mainLocale;

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
     * @param \ezp\Base\Locale $mainLocale
     */
    public function __construct( Type $contentType, Locale $mainLocale )
    {
        $this->properties = new ContentValue( array( 'typeId' => $contentType->id ) );
        /*
        @TODO Make sure all dynamic properties writes to value object if scalar value (creationDate (int)-> properties->created )
        */
        $this->mainLocale = $mainLocale;
        $this->versions = new TypeCollection( 'ezp\\Content\\Version' );
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        $this->relations = new TypeCollection( 'ezp\\Content' );
        $this->reversedRelations = new TypeCollection( 'ezp\\Content' );
        $this->translations = new TypeCollection( 'ezp\\Content\\Translation' );
        $this->contentType = $contentType;
        $this->addTranslation( $mainLocale );
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
        $resultArray = array();
        foreach ( $this->translations as $tr )
        {
            $resultArray = array_merge( $resultArray, (array)$tr->versions );
        }
        return new TypeCollection( 'ezp\\Content\\Version', $resultArray );
    }

    /**
     * Find current version amongst version objects
     *
     * @return \ezp\Content\Version|null
     */
    protected function getCurrentVersion()
    {
        foreach ( $this->translations[$this->mainLocale->code]->versions as $contentVersion )
        {
            if ( $this->currentVersion == $contentVersion->version )
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
     * Returns the section's id
     *
     * @return int
     */
    protected function getSectionId()
    {
        if ( $this->section instanceof Proxy || $this->section instanceof Section )
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
     * @return \ezp\Content\Translation
     * @throw InvalidArgumentException if translation in $base does not exist.
     */
    public function addTranslation( Locale $locale, Version $base = null )
    {
        if ( isset( $this->translations[$locale->code] ) )
        {
            throw new InvalidArgumentException( "Translation {$locale->code} already exists" );
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
     * Remove the translation in $locale
     *
     * @param \ezp\Base\Locale $locale
     * @throw InvalidArgumentException if the main locale is the one in
     *          argument or if there's not translation
     *          in this locale @todo Use Base exceptions
     */
    public function removeTranslation( Locale $locale )
    {
        if ( $locale->code === $this->mainLocale->code )
        {
            throw new InvalidArgumentException( "Transation {$locale->code} is the main locale of this Content so it cannot be removed" );
        }
        if ( !isset( $this->translations[$locale->code] ) )
        {
            throw new InvalidArgumentException( "Transation {$locale->code} does not exist so it cannot be removed" );
        }
        unset( $this->translations[$locale->code] );
        // @todo ? remove on each versions in $this->translations[$locale->code]
        //foreach ( $this->translations[$locale->code]->versions as $version )
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
     * Clone content object
     */
    public function __clone()
    {
        $this->id = false;
        $this->status = self::STATUS_DRAFT;

        // Get the location's, so that new content will be the old one's sibling
        $oldLocations = $this->locations;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        foreach ( $oldLocations as $location )
        {
            $this->addParent( $location->parent );
        }
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
