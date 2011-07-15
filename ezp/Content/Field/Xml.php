<?php
/**
 * XML Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Type\Field\Xml as XmlDefinition;

/**
 * XML Field value object class
 */
class Xml extends Text
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezxmltext';

    /**
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Xml $fieldDefinition
     */
    public function __construct( Version $contentVersion, XmlDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
    }
}
