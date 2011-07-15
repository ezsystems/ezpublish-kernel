<?php
/**
 * Keyword Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Interfaces\ContentFieldDefinition;

/**
 * Keyword Field value object class
 */
class Keyword extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezkeyword';

    /**
     * @see ezp\Content\Interfaces\ContentFieldType
     */
    public function __construct( ContentFieldDefinition $contentTypeFieldType )
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
