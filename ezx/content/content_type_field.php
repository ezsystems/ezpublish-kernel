<?php
/**
 * Abstract Content Type Field (content class attribute) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * @Entity @Table(name=" ezcontentclass_attribute")
 *
 * @property-read string $fieldTypeString
 */
namespace ezx\content;
class ContentTypeField extends \ezp\content\AbstractField
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'data_text1' => false,
        'data_text2' => false,
        'data_text3' => false,
        'data_text4' => false,
        'data_text5' => false,
        'data_int1' => false,
        'data_int2' => false,
        'data_int3' => false,
        'data_int4' => false,
        'data_float1' => false,
        'data_float2' => false,
        'data_float3' => false,
        'data_float4' => false,
        'identifier' => true,
        'fieldTypeString' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'type' => true,
        'contentType' => false,
        'contentFields' => false,
    );

    /**
     * Constructor, sets up empty contentFields collection and attach $contentType
     *
     * @param ContentType $contentType
     */
    public function __construct( ContentType $contentType )
    {
        $this->contentType = $contentType;
        $this->contentFields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * @Id @Column(type="integer")
     * @var int
     */
    protected $version;

    /**
     * @Column(type="integer", name="contentclass_id")
     * @var int
     */
    protected $contentTypeId;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $identifier;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text1;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text2;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text3;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text4;

    /**
     * @Column(length=50)
     * @var string
     */
    protected $data_text5;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int1;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int2;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int3;

    /**
     * @Column(type="integer")
     * @var int
     */
    protected $data_int4;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float1;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float2;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float3;

    /**
     * @Column(type="float")
     * @var float
     */
    protected $data_float4;

    /**
     * @Column(length=50, name="data_type_string")
     * @var string
     */
    protected $fieldTypeString;


    /**
     * @Column(type="integer")
     * @var int
     */
    protected $placement;

    /**
     * @ManyToOne(targetEntity="ContentType", inversedBy="fields")
     * @JoinColumn(name="contentclass_id", referencedColumnName="id")
     * @var ContentType
     */
    protected $contentType;

    /**
     * Return content type object
     *
     * @return ContentType
     */
    protected function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @OneToMany(targetEntity="Field", mappedBy="contentTypeField")
     * @var Field[]
     */
    protected $contentFields;

    /**
     * Return collection of all fields assigned to object (all versions and languages)
     *
     * @return Field[]
     */
    protected function getContentFields()
    {
        return $this->contentFields;
    }

    /**
     * Get mapping of type/definition identifier to class
     *
     * @return array
     */
    protected function getTypeList()
    {
        return \ezp\base\Configuration::getInstance('content')->get( 'fields', 'Definition' );
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\base\ObservableInterface $subject
     * @param string $event
     * @return ContentTypeField
     */
    public function update( \ezp\base\ObservableInterface $subject, $event = 'update' )
    {
        if ( $subject instanceof ContentType )
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
        return  $this->id . ' ' . $this->version . ' (' . $this->identifier . ')';
    }
}