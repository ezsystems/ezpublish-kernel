<?php
/**
 * String Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Field as ContentField,
    ezp\Content\Type\Field\String as StringDefinition;

/**
 * Float Field value object class
 */
class String extends ContentField
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
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\String $fieldDefinition
     */
    public function __construct( Version $contentVersion, StringDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
        $this->readableProperties['value'] = true;
        if ( isset( $fieldDefinition->default ) )
            $this->value = $fieldDefinition->default;
    }
}
