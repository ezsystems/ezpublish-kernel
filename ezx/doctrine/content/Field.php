<?php
/**
 * Abstract Content Field (content attribute) model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * @Entity @Table(name="ezcontentobject_attribute")
 */
namespace ezx\doctrine\content;
class Field extends Abstract_Field
{
    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\doctrine\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'id' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'version' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'data_text' => array(
            'type' => self::TYPE_STRING,
            'internal' => true,
        ),
        'data_int' => array(
            'type' => self::TYPE_INT,
            'internal' => true,
        ),
        'data_float' => array(
            'type' => self::TYPE_FLOAT,
            'internal' => true,
        ),
        'fieldTypeString' => array(
            'type' => self::TYPE_STRING,
        ),
        'type' => array(
            'type' => self::TYPE_OBJECT,
            'member' => true,
            'dynamic' => true,
        ),
        'content' => array(
            'type' => self::TYPE_OBJECT,
            'dynamic' => true,
        ),
    );

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $id = 0;

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $version = 0;

    /**
     * @Column(type="integer", name="contentobject_id")
     * @var int
     */
    protected $contentId = 0;

    /**
     * @Column(type="string")
     * @var string
     */
    protected $data_text = '';

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int = 0;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float = 0.0;

    /**
     * @Column(length=20, name="language_code")
     * @var string
     */
    protected $languageCode = '';

    /**
     * @Column(length=50, name="data_type_string")
     * @var string
     */
    protected $fieldTypeString = '';

    /**
     * @ManyToOne(targetEntity="Content", inversedBy="fields")
     * @JoinColumn(name="contentobject_id", referencedColumnName="id")
     * @var Content
     */
    protected $content;

    /**
     * Return content object
     *
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @ManyToOne(targetEntity="ContentTypeField", inversedBy="contentFields", fetch="EAGER")
     * @JoinColumn(name="contentclassattribute_id", referencedColumnName="id")
     * @var ContentTypeField
     */
    protected $contentTypeField;

    /**
     * Return content type object
     *
     * @return ContentTypeField
     */
    public function getContentTypeField()
    {
        return $this->contentTypeField;
    }

    /**
     * Constructor, sets up relation properties
     *
     * @param ContentTypeField $contentTypeField
     */
    public function __construct( Content $content, ContentTypeField $contentTypeField )
    {
        $this->content = $content;
        $this->contentTypeField = $contentTypeField;
        $this->fieldTypeString = $contentTypeField->fieldTypeString;
    }

    /**
     * Initialize field type class
     *
     * @throws \RuntimeException If $className is not instanceof Abstract_FieldType
     * @param string $className
     * @return Abstract_FieldType
     */
    protected function initType( $className )
    {
        $type = new $className( $this->getContentTypeField()->getType() );
        if ( !$type instanceof Abstract_FieldType )
            throw new \RuntimeException( "Field type value '{$className}' does not implement Abstract_FieldType" );
        if ( $this->version )
            $this->toType( $type );
        else
            $this->fromType( $type );
        return $type;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezx\doctrine\Interface_Observable $subject
     * @param string|null $event
     * @return Field
     */
    public function update( \ezx\doctrine\Interface_Observable $subject , $event  = null )
    {
        if ( $subject instanceof Content )
        {
            $this->notify( $event );
            return $this;
        }
        return parent::update( $subject, $event );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return  $this->id . ' ' . $this->version . ' ' . $this->languageCode . ' ' . $this->fieldTypeString;
    }
}
