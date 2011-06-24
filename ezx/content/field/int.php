<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Int Field value object class
 */
namespace ezx\content\Field;
class Int extends \ezx\content\Abstracts\FieldType implements \ezx\content\ContentFieldTypeInterface
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
     * @var \ezx\content\Abstracts\FieldType
     */
    protected $contentTypeFieldType;

    /**
     * Constructor
     *
     * @see \ezx\content\ContentFieldTypeInterface
     * @param \ezx\content\Abstracts\FieldType $contentTypeFieldType
     */
    public function __construct( \ezx\content\Abstracts\FieldType $contentTypeFieldType )
    {
        if ( isset( $contentTypeFieldType->default ) )
            $this->value = $contentTypeFieldType->default;

        $this->contentTypeFieldType = $contentTypeFieldType;
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
