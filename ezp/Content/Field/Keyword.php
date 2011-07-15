<?php
/**
 * Keyword Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

/**
 * Keyword Field value object class
 */
namespace ezp\Content\Field;
class Keyword extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezkeyword';

    /**
     * @see \ezp\Content\Interfaces\ContentFieldType
     */
    public function __construct( \ezp\Content\Interfaces\ContentFieldDefinition $contentTypeFieldType )
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct( $contentTypeFieldType );
    }
}
