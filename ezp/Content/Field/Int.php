<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Field as ContentField,
    ezp\Content\Type\Field\Int as IntDefinition;

/**
 * Int Field value object class
 */
class Int extends ContentField
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
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Int $fieldDefinition
     */
    public function __construct( Version $contentVersion, IntDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
        $this->readWriteProperties['value'] = true;
        if ( isset( $fieldDefinition->default ) )
            $this->value = $fieldDefinition->default;
    }
}
