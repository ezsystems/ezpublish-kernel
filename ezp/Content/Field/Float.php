<?php
/**
 * Float Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Field as ContentField,
    ezp\Content\Type\Field\Float as FloatDefinition;

/**
 * Float Field value object class
 */
class Float extends ContentField
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezfloat';

    /**
     * @public
     * @var float
     */
    public $value = 0.0;

    /**
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Float $fieldDefinition
     */
    public function __construct( Version $contentVersion, FloatDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
        $this->readableProperties['value'] = true;
        if ( isset( $fieldDefinition->default ) )
            $this->value = $fieldDefinition->default;
    }
}
