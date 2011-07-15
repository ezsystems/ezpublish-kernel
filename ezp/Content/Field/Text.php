<?php
/**
 * Image Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Type\Field\Text as TextDefinition;

/**
 * Image Field value object class
 */
class Text extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'eztext';

    /**
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Text $fieldDefinition
     */
    public function __construct( Version $contentVersion, TextDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
    }
}
