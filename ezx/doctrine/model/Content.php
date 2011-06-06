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
    protected static $definition = array(
        'id' => array( 'type' => self::TYPE_INT, 'readonly' => true, 'internal' => true ),
        'currentVersion' => array( 'type' => self::TYPE_INT, 'readonly' => true, 'internal' => true ),
        'ownerId' => array( 'type' => self::TYPE_INT ),
        'sectionId' => array( 'type' => self::TYPE_INT ),
        'fields' => array( 'type' => self::TYPE_ARRAY ),
        'locations' => array( 'type' => self::TYPE_ARRAY ),
        'contentType' => array( 'type' => self::TYPE_OBJECT, 'internal' => true ),
    );

    /**
     * Create content based on content type object
     *
     * @param ContentType $contentType
     */
    public function __construct( ContentType $contentType )
    {
        $this->locations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();

        $this->typeId = $contentType->id;
        $this->contentType = $contentType;
        foreach ( $contentType->getFields() as $contentTypeField )
        {
            $field = new Field( $this, $contentTypeField );
            $field->fromHash( array(
                'fieldTypeString' => $contentTypeField->fieldTypeString,
            ));
            $fieldValue = $field->getValueObject();
            if ( $fieldValue instanceof Interface_Field_Init )
            {
                $fieldValue->init( $contentTypeField->getValueObject() );
            }
            $this->fields[] = $field;
        }
        return $this->postLoad();
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
    private $fields;

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
    protected $fieldMap;

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
            if ( $this->fieldMap !== null )
                return $this->fieldMap;
            return $this->fieldMap = new FieldMap( $this );
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
