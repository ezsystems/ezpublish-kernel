<?php
/**
 * Keyword Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Content\Version,
    ezp\Content\Type\Field\Author as AuthorDefinition;

/**
 * Keyword Field value object class
 */
class Author extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezauthor';

    /**
     * @param \ezp\Content\Version $contentVersion
     * @param \ezp\Content\Type\Field\Author $fieldDefinition
     */
    public function __construct( Version $contentVersion, AuthorDefinition $fieldDefinition )
    {
        parent::__construct( $contentVersion, $fieldDefinition );
    }
}
