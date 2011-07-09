<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage content
 */

/**
 * Int Field value object class
 */
namespace ezp\Content\Field;
class Int extends \ezp\Content\AbstractFieldType implements \ezp\Content\Interfaces\ContentFieldType
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
     * @var \ezp\Content\Interfaces\ContentFieldDefinition
     */
    protected $contentTypeFieldType;

    /**
     * @see \ezp\Content\Interfaces\ContentFieldType
     */
    public function __construct( \ezp\Content\Interfaces\ContentFieldDefinition $contentTypeFieldType )
    {
        if ( isset( $contentTypeFieldType->default ) )
            $this->value = $contentTypeFieldType->default;

        $this->contentTypeFieldType = $contentTypeFieldType;
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
