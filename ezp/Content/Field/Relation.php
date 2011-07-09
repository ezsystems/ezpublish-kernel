<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage content
 */

/**
 * Relation Field value object class
 */
namespace ezp\Content\Field;
class Relation extends Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezobjectrelation';

    /**
     * @see \ezp\Content\Interfaces\ContentFieldType
     */
    public function __construct( \ezp\Content\Interfaces\ContentFieldDefinition $contentTypeFieldType )
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
