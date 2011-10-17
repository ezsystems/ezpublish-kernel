<?php
/**
 * File containing the ezp\Content\Version\Concrete class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Version;
use ezp\Base\Model,
    ezp\Content,
    ezp\Content\Version,
    ezp\Content\Field\StaticCollection as FieldCollection,
    ezp\Persistence\Content\Version as VersionValue;

/**
 * This class represents a Concrete Content Version
 *
 * @property-read int $id
 * @property-read int $versionNo
 * @property-read mixed $contentId
 * @property-read int $status One of the STATUS_* constants
 * @property-read \ezp\Content $content
 * @property-read string[] $name Content's name
 * @property-read mixed $ownerId Content's Owner id
 * @property-read bool $alwaysAvailable Content's always available flag
 * @property-read string $remoteId Content's Remote ID
 * @property-read mixed $sectionId Content' Section ID
 * @property-read mixed $typeId Content's Type ID
 * @property-read int $contentStatus Content' status, as one of the \ezp\Content::STATUS_* constants
 * @property-read \ezp\Content\Location $mainLocation Content's main location
 * @property-read \ezp\Content\Section $section Content's Section
 * @property-read \ezp\User $owner Content's Owner
 * @property-read \ezp\Content\Type $contentType Content's type
 * @property-read \ezp\Content\Location[] $locations Content's locations
 * @property-read \ezp\Content\Relation[] $relations Content's relations
 * @property-read \ezp\Content\Relation[] $reverseRelations Content's reverse relations
 * @property-read \ezp\Content\Language $initialLanguage Content's initial language
 * @property mixed $initialLanguageId
 * @property int $creatorId
 * @property int $created
 * @property int $modified
 * @property-read \ezp\Content\Field[] $fields An hash structure of fields
 */
class Concrete extends Model implements Version
{

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'versionNo' => false,
        'creatorId' => true,
        'created' => true,
        'modified' => true,
        'status' => false,
        'contentId' => false,
        'initialLanguageId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'content' => false,
        // @todo (copied from Content\Concrete): Make readOnly and generate on store event from attributes based on type nameScheme
        'name' => false,
        'ownerId' => false,
        'alwaysAvailable' => false,
        // @todo (copied from Content\Concrete): Make readonly and deal with this internally (in all DO's)
        'remoteId' => false,
        'sectionId' => false,
        'typeId' => false,
        'contentStatus' => false,
        /*
        @todo: Implement as soon as there is some more info (like @property) in Content\Concrete
        'contentModified' => false,
        'contentPublished' => false,
        */
        'mainLocation' => false,
        'section' => false,
        'owner' => false,
        'contentType' => false,
        'locations' => false,
        'relations' => false,
        'reverseRelations' => false,
        'initialLanguage' => false,
    );

    /**
     * @var \ezp\Content\Field[]
     */
    protected $fields;

    /**
     * Content object this version is attached to.
     *
     * @var Content
     */
    protected $content;

    /**
     * Create content version based on content and content type fields objects
     *
     * @param Content $content
     */
    public function __construct( Content $content )
    {
        $this->properties = new VersionValue(
            array(
                'contentId' => $content->id,
                'status' => self::STATUS_DRAFT,
                'initialLanguageId' => $content->initialLanguageId
            )
        );
        $this->content = $content;
        $this->fields = new FieldCollection( $this );
    }

    /**
     * Get fields of current version
     *
     * @return \ezp\Content\Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get content that this version is attached to
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get content's name that this version is attached to
     *
     * @return string[]
     */
    public function getName()
    {
        return $this->content->name;
    }

    /**
     * Get content's Owner ID that this version is attached to
     *
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->content->ownerId;
    }

    /**
     * Get content's "always available" flag that this version is attached to
     *
     * @return bool
     */
    public function getAlwaysAvailable()
    {
        return $this->content->alwaysAvailable;
    }

    /**
     * Get content's remote ID that this version is attached to
     *
     * @return string
     */
    public function getRemoteId()
    {
        return $this->content->remoteId;
    }

    /**
     * Get content's section ID that this version is attached to
     *
     * @return mixed
     */
    public function getSectionId()
    {
        return $this->content->sectionId;
    }

    /**
     * Get content's section ID that this version is attached to
     *
     * @return mixed
     */
    public function getTypeId()
    {
        return $this->content->typeId;
    }

    /**
     * Get content' status that this version is attached to, as one of the \ezp\Content::STATUS_* constants
     *
     * @return int
     */
    public function getContentStatus()
    {
        return $this->content->status;
    }

    /**
     * Get content's main location that this version is attached to
     *
     * @return \ezp\Content\Location|null
     */
    public function getContentMainLocation()
    {
        return $this->content->getMainLocation();
    }

    /**
     * Get content' section that this version is attached to
     *
     * @return \ezp\Content\Section
     */
    public function getSection()
    {
        return $this->content->getSection();
    }

    /**
     * Get content's owner that this version is attached to
     *
     * @return \ezp\User
     */
    public function getOwner()
    {
        return $this->content->getOwner();
    }

    /**
     * Get content's type this version is attached to
     *
     * @return \ezp\Content\Type
     */
    public function getContentType()
    {
        return $this->content->getContentType();
    }

    /**
     * Get content's locations this version is attached to
     *
     * @return \ezp\Content\Location[]
     */
    public function getLocations()
    {
        return $this->content->getLocations();
    }

    /**
     * Get content's locations this version is attached to
     *
     * @return \ezp\Content\Relation[]
     */
    public function getRelations()
    {
        return $this->content->getRelations();
    }

    /**
     * Get content's locations this version is attached to
     *
     * @return \ezp\Content\Relation[]
     */
    public function getReverseRelations()
    {
        return $this->content->getReverseRelations();
    }

    /**
     * Get content's initial language ID this version is attached to
     *
     * @return mixed
     */
    public function getInitialLanguage()
    {
        return $this->content->getInitialLanguage();
    }
}
