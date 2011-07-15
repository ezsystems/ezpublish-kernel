<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Type\Field\Datetime as DatetimeDefinition;

/**
 * Relation Field value object class
 */
class Datetime extends Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezdatetime';

    /**
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Datetime $fieldDefinition
     */
    public function __construct( Version $contentVersion, DatetimeDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
    }
}
