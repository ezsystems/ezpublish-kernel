<?php
/**
 * File containing the ezp\Content\Location\Trashed class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location;
use ezp\Content,
    ezp\Content\Location\Concrete as ConcreteLocation,
    ezp\Persistence\Content\Location\Trashed as TrashedLocationValue,
    ezp\Base\Exception\InvalidArgumentType;

class Trashed extends ConcreteLocation
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'priority' => false,
        'hidden' => false,
        'invisible' => false,
        'remoteId' => false,
        'contentId' => false,
        'parentId' => false,
        'pathIdentificationString' => false,
        'pathString' => false,
        'modifiedSubLocation' => false,
        'mainLocationId' => false,
        'depth' => false,
        'sortField' => false,
        'sortOrder' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'content' => false,
        'parent' => false,
        'children' => false
    );
}
