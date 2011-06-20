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
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\base\Abstracts\DomainObject::$definition
     * @var array
     */
    protected static $definition = array(
        'id' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'currentVersion' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'name' => array(
            'type' => self::TYPE_STRING,
        ),
        'ownerId' => array(
            'type' => self::TYPE_INT,
        ),
        'sectionId' => array(
            'type' => self::TYPE_INT,
        ),
        'versions' => array(
            'type' => self::TYPE_ARRAY,
            'member' => true,
            'dynamic' => true,
        ),
        'locations' => array(
            'type' => self::TYPE_ARRAY,
            'member' => true,
            'dynamic' => true,
        ),
        'contentType' => array(
            'type' => self::TYPE_OBJECT,
            'dynamic' => true,
        ),
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
     * @var \Doctrine\Common\Collections\ArrayCollection(Location)
     */
    protected $locations;

    /**
     * Return collection of all locations attached to this object
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(Location)
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @OneToMany(targetEntity="ContentVersion", mappedBy="content", fetch="EAGER")
     * @var \Doctrine\Common\Collections\ArrayCollection(ContentVersion)
     */
    private $versions;

    /**
     * Return collection of all content versions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentVersion)
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
     * @return \Doctrine\Common\Collections\ArrayCollection(ContentType)
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Shortcut to ->currentVersion()->fields
     *
     * @var FieldMap(ContentField)
     */
    private $fields;

    /**
     * Get value
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( $name === 'fields' )
        {
            return $this->getCurrentVersion()->fields;
        }
        return parent::__get( $name );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id . ' ('  . $this->name . ')';
    }
}
