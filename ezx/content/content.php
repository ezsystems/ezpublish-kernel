<?php
/**
 * Content (content object) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentobject")
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
 * @property-read int $currentVersion
 * @property-read string $name
 * @property int $ownerId
 * @property int $sectionId
 * @property-read string $typeid Content Type Identifier
 * @property-read Location[] $locations An hash like structure of fields
 * @property-read ContentField[] $fields An hash structure of fields
 * @property-read ContentType $type Content type object
 */
namespace ezx\content;
class Content extends Abstracts\ContentModel
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
        'name' => false,
        'ownerId' => true,
        'sectionId' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'locations' => true,
        'contentType' => false,
        'versions' => false,
    );

    /**
     * Create content based on content type object
     *
     * @param ContentType $contentType
     */
    public function __construct( ContentType $contentType )
    {
        $this->locations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->versions  = new \Doctrine\Common\Collections\ArrayCollection();

        $this->typeId = $contentType->id;
        $this->contentType = $contentType;
        $this->versions[] = new ContentVersion( $this );
        $this->postLoad();
    }

    /**
     * Setup locations and fields as observers of content object
     *
     * @PostLoad
     * @internal Only for use by create function
     * @return Content
     */
    protected function postLoad()
    {
        foreach( $this->getLocations() as $location )
        {
            $this->attach( $location, 'store' );
        }
        foreach( $this->getVersions() as $version )
        {
            $this->attach( $version, 'store' );
        }
        return $this;
    }

    /**
     * @Id @Column(type="integer") @GeneratedValue
     * @var int
     */
    protected $id = 0;

    /**
     * @Column(type="integer", name="current_version")
     * @var int
     */
    protected $currentVersion = 0;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $name = '';

    /**
     * @Column(type="integer", name="owner_id")
     * @var int
     */
    public $ownerId = 0;

    /**
     * @Column(type="integer", name="section_id")
     * @var int
     */
    public $sectionId = 0;

    /**
     * @Column(type="integer", name="contentclass_id")
     * @var int
     */
    protected $typeId = 0;

    /**
     * @OneToMany(targetEntity="Location", mappedBy="content", fetch="EAGER")
     * @var Location[]
     */
    protected $locations;

    /**
     * Return collection of all locations attached to this object
     *
     * @return Location[]
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @OneToMany(targetEntity="ContentVersion", mappedBy="content", fetch="EAGER")
     * @var ContentVersion[]
     */
    protected $versions;

    /**
     * Return collection of all content versions
     *
     * @return ContentVersion[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Find current version amongst version objects
     *
     * @return ContentVersion|null
     */
    public function getCurrentVersion()
    {
        foreach( $this->getVersions() as $contentVersion )
        {
            if ( $this->currentVersion == $contentVersion->version )
                return $contentVersion;
        }
        return null;
    }

    /**
     * @ManyToOne(targetEntity="ContentType", inversedBy="contentObjects")
     * @JoinColumn(name="contentclass_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $contentType;

    /**
     * Return ContentType object
     *
     * @return ContentType
     */
    public function getContentType()
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
     * @return mixed
     */
    protected function getFields()
    {
        return $this->getCurrentVersion()->fields;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id . ' ('  . $this->name . ')';
    }
}
