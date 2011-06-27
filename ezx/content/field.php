<?php
/**
 * Abstract Content Field (content attribute) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name="ezcontentobject_attribute")
 */
namespace ezx\content;
class Field extends \ezp\content\AbstractField
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'data_text' => false,
        'data_int' => false,
        'data_float' => false,
        'fieldTypeString' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'type' => true,
        'contentVersion' => false,
        'contentTypeField' => false,
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
     * @Column(type="integer")
     * @var int
     */
    //protected $contentobject_id = 0;

    /**
     * @ManyToOne(targetEntity="ContentVersion", inversedBy="fields")
     * @joinColumns(@JoinColumn(name="contentobject_id", referencedColumnName="contentobject_id"),@JoinColumn(name="version", referencedColumnName="version"))
     * @var ContentVersion
     */
    protected $contentVersion;

    /**
     * Return content version object
     *
     * @return ContentVersion
     */
    protected function getContentVersion()
    {
        return $this->contentVersion;
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
    protected function getContentTypeField()
    {
        return $this->contentTypeField;
    }

    /**
     * Constructor, sets up properties
     *
     * @param ContentVersion $contentVersion
     * @param ContentTypeField $contentTypeField
     */
    public function __construct( ContentVersion $contentVersion, ContentTypeField $contentTypeField )
    {
        $this->contentVersion = $contentVersion;
        $this->contentTypeField = $contentTypeField;
        $this->fieldTypeString = $contentTypeField->fieldTypeString;
    }

    /**
     * Initialize field type class
     *
     * @throws \RuntimeException If $className is not instanceof Abstracts\FieldType
     * @param string $className
     * @return Abstracts\FieldType
     */
    protected function initType( $className )
    {
        $type = new $className( $this->getContentTypeField()->getType() );
        if ( !$type instanceof \ezp\content\AbstractFieldType )
            throw new \RuntimeException( "Field type value '{$className}' does not implement ezx\\content\\Abstracts\\FieldType" );
        if ( $this->version )
            $this->toType( $type );
        else
            $this->fromType( $type );
        return $type;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\base\ObservableInterface $subject
     * @param string $event
     * @return Field
     */
    public function update( \ezp\base\ObservableInterface $subject, $event = 'update' )
    {
        if ( $subject instanceof ContentVersion )
        {
            return $this->notify( $event );
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
