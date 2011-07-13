<?php
/**
 * File contains Interface for Content Field Type
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Interfaces;

/**
 * Interface for Content Field Type (Content Attribute Datatype class)
 *
 */
interface ContentFieldType
{
    /**
     * Called when content object field type is constructed
     *
     * This function can safely set default values, as values from db will be set afterwards if this is not a new object
     *
     * @param ContentFieldDefinitionInterface $contentTypeFieldType
     */
    public function __construct( ContentFieldDefinition $contentTypeFieldType );
}
