<?php
/**
 * File contains Content Type Field (content class attribute) class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Content Type Field (content class attribute) class
 *
 * @property-read string $fieldTypeString
 */
namespace ezp\content;
class ContentTypeField extends AbstractField
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'contentTypeId' => false,
        'identifier' => true,
        'fieldTypeString' => true,
        'contentFields' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'type' => true,
        'contentType' => false,
    );

    /**
     * Constructor, sets up empty contentFields collection and attach $contentType
     *
     * @param ContentType $contentType
     */
    public function __construct( ContentType $contentType )
    {
        $this->contentType = $contentType;
        $this->contentFields = new \ezp\base\TypeCollection( '\ezp\content\Field' );
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var int
     */
    protected $contentTypeId;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $data_text1;

    /**
     * @var string
     */
    protected $data_text2;

    /**
     * @var string
     */
    protected $data_text3;

    /**
     * @var string
     */
    protected $data_text4;

    /**
     * @var string
     */
    protected $data_text5;

    /**
     * @var int
     */
    protected $data_int1;

    /**
     * @var int
     */
    protected $data_int2;

    /**
     * @var int
     */
    protected $data_int3;

    /**
     * @var int
     */
    protected $data_int4;

    /**
     * @var float
     */
    protected $data_float1;

    /**
     * @var float
     */
    protected $data_float2;

    /**
     * @var float
     */
    protected $data_float3;

    /**
     * @var float
     */
    protected $data_float4;

    /**
     * @var string
     */
    protected $fieldTypeString;


    /**
     * @var int
     */
    protected $placement;

    /**
     * @var ContentField[]
     */
    protected $contentFields;

    /**
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
        if ( $this->contentType instanceof Proxy )
        {
            $this->contentType = $this->contentType->load();
        }
        return $this->contentType;
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
        return $this->identifier;
    }
}