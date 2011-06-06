<?php
/**
 * Content (content object) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
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
 * @property-read array(Location) $locations An hash like structure of fields
 * @property-read array(string => Field) $fields An hash structure of fields
 * @property-read ContentType $type Content type object
 */
namespace ezx\doctrine\model;
class Content extends Abstract_ContentModel
{
    protected $_aggregateMembers = array( 'fields', 'locations' );

    /**
     * Constructs a new instance of this class, protected, use factory on ContentRepository.
     */
    protected function __construct()
    {
        $this->locations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Setup locations and fields as observers of content object
     *
     * @PostLoad
     * @internal Only for use by create function
     * @return Content
     */
    public function _postLoad()
    {
        foreach( $this->__get( 'locations' ) as $location )
        {
            $this->attach( $location );
        }
        foreach( $this->__get( 'fields' ) as $field )
        {
            $this->attach( $field );
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
    protected $ownerId = 0;

    /**
     * @Column(type="integer", name="section_id")
     * @var int
     */
    protected $sectionId = 0;

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
     * @OneToMany(targetEntity="Field", mappedBy="content", fetch="EAGER")
     * @var FieldMap(Field)
     */
    protected $fields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return \Doctrine\Common\Collections\ArrayCollection(ContenField)
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @ManyToOne(targetEntity="ContentType", inversedBy="contentObjects")
     * @JoinColumn(name="contentclass_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $contentType;

    /**
     * Magic object that steps in when fields are accessed
     */
    protected $_fieldMap;

    /**
     * Create content based on content type object
     *
     * @param ContentType $contentType
     * @return Content
     */
    static public function create( ContentType $contentType )
    {
        $content = new self();
        $content->typeId = $contentType->id;
        $content->contentType = $contentType;
        foreach ( $contentType->getFields() as $contentTypeField )
        {
            $field = new Field();
            $field->setState( array(
                'fieldTypeString' => $contentTypeField->fieldTypeString,
                'contentTypeField' => $contentTypeField,
                'content' => $content,
            ));
            $field->getValueObject()->init( $contentTypeField->getValueObject() );
            $content->fields[] = $field;
        }
        return $content->_postLoad();
    }

    /**
     * Set value
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @param string $value
     * @return mixed Return $value
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'ownerId':
            case 'sectionId':
                return $this->$name = $value;
            default:
                if ( isset( $this->$name ) )
                    throw new \InvalidArgumentException( "{$name} is not a writable property on " . __CLASS__ );
                else
                    throw new \InvalidArgumentException( "{$name} is not a valid property on " . __CLASS__ );
        }
    }

    /**
     * Get value
     *
     * @throws \InvalidArgumentException
     * @param string $name
     * @param string $value
     * @return mixed Return $value
     */
    public function __get( $name )
    {
        if ( $name === 'fields' )
        {
            if ( $this->_fieldMap !== null )
                return $this->_fieldMap;
            return $this->_fieldMap = new FieldMap( $this );
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
