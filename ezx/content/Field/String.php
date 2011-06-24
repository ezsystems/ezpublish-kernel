<?php
/**
 * String Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Float Field value object class
 */
namespace ezx\content;
class Field_String extends Abstracts\FieldType implements Interfaces\ContentFieldType
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezstring';

    /**
     * @public
     * @var string
     */
    public $value = '';

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'value' => 'data_text',
    );

    /**
     * @var Abstracts\FieldType
     */
    protected $contentTypeFieldType;

    /**
     * Constructor
     *
     * @see Interfaces\ContentFieldType
     * @param Abstracts\FieldType $contentTypeFieldType
     */
    public function __construct( Abstracts\FieldType $contentTypeFieldType )
    {
        if ( isset( $contentTypeFieldType->default ) )
            $this->value = $contentTypeFieldType->default;

        $this->contentTypeFieldType = $contentTypeFieldType;
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}