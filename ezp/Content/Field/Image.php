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
    ezp\Content\Type\Field\Image as ImageDefinition;

/**
 * Image Field value object class
 */
class Image extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezimage';

    /**
     * @see ezp\Content\Interfaces\ContentFieldType
     */
    protected function init( ContentFieldDefinition $fieldDefinition )
    {
        parent::init( $fieldDefinition );
    }
}
