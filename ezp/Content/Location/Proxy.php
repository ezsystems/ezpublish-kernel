<?php
/**
 * File containing the ezp\Content\Location\Proxy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location;
use ezp\Base\Proxy as BaseProxy,
    ezp\Content,
    ezp\Content\Location;

/**
 * This class represents a Proxy Content Location
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
 * @property int $sortField Sort field int, to be compared with \ezp\Content\ContainerProperty::SORT_FIELD_* constants
 * @property int $sortOrder Sort order int, to be compared with \ezp\Content\ContainerProperty::SORT_ORDER_* constants
 * @property-read \ezp\Content\Location[] $children Location's children in subtree
 * @property \ezp\Content $content Associated Content object
 * @property \ezp\Content\Location $parent Location's parent location
 */
class Proxy extends BaseProxy implements Location
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    /**
     * Returns the parent Location
     *
     * @return \ezp\Content\Location
     */
    public function getParent()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getParent();
    }

    /**
     * Sets the parent Location and updates inverse side ( $parent->children )
     *
     * @param \ezp\Content\Location $parent
     */
    public function setParent( Location $parent )
    {
        $this->lazyLoad();
        return $this->proxiedObject->setParent( $parent );
    }

    /**
     * Returns the Content the Location holds
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getContent();
    }

    /**
     * Sets the content and updates inverse side ( $content->locations )
     *
     * @param \ezp\Content $content
     */
    public function setContent( Content $content )
    {
        $this->lazyLoad();
        return $this->proxiedObject->setContent( $content );
    }

    /**
     * Returns collection of children locations
     *
     * @return \ezp\Content\Location[]
     */
    public function getChildren()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getChildren();
    }
}
