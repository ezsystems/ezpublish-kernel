<?php
/**
 * File containing the ezp\Content\Location\Concrete interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Content;

/**
 * This interface represents a Content Location
 *
 * @property-read int $id
 * @property int $priority
 * @property-read bool $hidden
 * @property-read bool $invisible
 * @property-read string $remoteId
 * @property-read int $contentId
 * @property-read int $parentId
 * @property-read string $pathIdentificationString
 * @property-read string $pathString Path string for location (like /1/2/)
 * @property-read int $modifiedSubLocation
 * @property-read int $mainLocationId
 * @property-read int $depth
 * @property int $sortField Sort field int, to be compared with \ezp\Content\Location::SORT_FIELD_* constants
 * @property int $sortOrder Sort order int, to be compared with \ezp\Content\Location::SORT_ORDER_* constants
 * @property-read \ezp\Content\Location[] $children Location's children in subtree
 * @property \ezp\Content $content Associated Content object
 * @property \ezp\Content\Location $parent Location's parent location
 */
interface Location
{
    // TODO const from eZ Publish 4.5
    // needs update to reflect concept changes
    const SORT_FIELD_PATH = 1;
    const SORT_FIELD_PUBLISHED = 2;
    const SORT_FIELD_MODIFIED = 3;
    const SORT_FIELD_SECTION = 4;
    const SORT_FIELD_DEPTH = 5;
    const SORT_FIELD_CLASS_IDENTIFIER = 6;
    const SORT_FIELD_CLASS_NAME = 7;
    const SORT_FIELD_PRIORITY = 8;
    const SORT_FIELD_NAME = 9;
    const SORT_FIELD_MODIFIED_SUBNODE = 10;
    const SORT_FIELD_NODE_ID = 11;
    const SORT_FIELD_CONTENTOBJECT_ID = 12;

    const SORT_ORDER_DESC = 0;
    const SORT_ORDER_ASC = 1;

    /**
     * Returns the parent Location
     *
     * @return \ezp\Content\Location
     */
    public function getParent();

    /**
     * Sets the parent Location and updates inverse side ( $parent->children )
     *
     * @param \ezp\Content\Location $parent
     */
    public function setParent( Location $parent );

    /**
     * Returns the Content the Location holds
     *
     * @return \ezp\Content
     */
    public function getContent();

    /**
     * Sets the content and updates inverse side ( $content->locations )
     *
     * @param \ezp\Content $content
     */
    public function setContent( Content $content );

    /**
     * Returns collection of children locations
     *
     * @return \ezp\Content\Location[]
     */
    public function getChildren();
}
