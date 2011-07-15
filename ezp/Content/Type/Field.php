<?php
/**
 * File contains Content Type Field (content class attribute) class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Content\AbstractField,
    ezp\Base\TypeCollection,
    ezp\Base\Configuration,
    ezp\Base\Interfaces\Observable;

/**
 * Content Type Field (content class attribute) class
 *
 * @property-read string $fieldTypeString
 */
class Field extends AbstractField
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
        'contentFields' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'type' => true,
        'contentType' => false,
        'contentTypeId' => false,
    );

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    public $identifier;

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
    public $fieldTypeString;


    /**
     * @var int
     */
    public $placement;

    /**
     * @var ezp\Content\Field[]
     */
    protected $contentFields;

    /**
     * @var Type
     */
    protected $contentType;

    /**
     * Constructor, sets up empty contentFields collection and attach $contentType
     *
     * @param Type $contentType
     */
    public function __construct( Type $contentType )
    {
        $this->contentType = $contentType;
        $this->contentFields = new TypeCollection( 'ezp\\Content\\Field' );
    }

    /**
     * Return content type object
     *
     * @return Type
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
     * Return content type id
     *
     * @return int
     */
    protected function getContentTypeId()
    {
        if ( $this->contentType instanceof Proxy || $this->contentType instanceof Type )
        {
            return $this->contentType->id;
        }
        return 0;
    }

    /**
     * Get mapping of type/definition identifier to class
     *
     * @return array
     */
    protected function getTypeList()
    {
        return Configuration::getInstance( 'content' )->get( 'fields', 'Definition' );
    }

    /**
     * Called when subject has been updated
     *
     * @param ezp\Base\Interfaces\Observable $subject
     * @param string $event
     * @return Field
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $subject instanceof Type )
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
