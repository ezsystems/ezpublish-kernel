<?php
/**
 * String Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\AbstractFieldType,
    ezp\Content\Interfaces\ContentFieldType,
    ezp\Content\Interfaces\ContentFieldDefinition;

/**
 * Float Field value object class
 */
class String extends AbstractFieldType implements ContentFieldType
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
     * @var ContentFieldDefinition
     */
    protected $contentTypeFieldType;

    /**
     * @see ContentFieldType
     */
    public function __construct( ContentFieldDefinition $contentTypeFieldType )
    {
        if ( isset( $contentTypeFieldType->default ) )
            $this->value = $contentTypeFieldType->default;

        $this->contentTypeFieldType = $contentTypeFieldType;
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
