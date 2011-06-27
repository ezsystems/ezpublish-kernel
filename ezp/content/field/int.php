<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Int Field value object class
 */
namespace ezp\content\Field;
class Int extends \ezp\content\AbstractFieldType implements \ezp\content\ContentFieldTypeInterface
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezinteger';

    /**
     * @public
     * @var int
     */
    public $value = 0;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'value' => 'data_int',
    );

    /**
     * @var \ezp\content\ContentFieldDefinitionInterface
     */
    protected $contentTypeFieldType;

    /**
     * @see \ezp\content\ContentFieldTypeInterface
     */
    public function __construct( \ezp\content\ContentFieldDefinitionInterface $contentTypeFieldType )
    {
        if ( isset( $contentTypeFieldType->default ) )
            $this->value = $contentTypeFieldType->default;

        $this->contentTypeFieldType = $contentTypeFieldType;
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
