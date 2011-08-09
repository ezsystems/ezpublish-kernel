<?php
/**
 * File containing the ezp\Content\Location class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Observer,
    ezp\Base\TypeCollection,
    ezp\Base\Observable,
    ezp\Content,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * This class represents a Content Location
 *
 * @property int $priority
 * @property string $remoteId
 * @property int $sortField Sort field int, to be compared with \ezp\Content\ContainerProperty::SORT_FIELD_* constants
 * @property int $sortOrder Sort order int, to be compared with \ezp\Content\ContainerProperty::SORT_ORDER_* constants
 * @property-read int $id
 * @property-read bool $hidden
 * @property-read bool $invisible
 * @property-read string $pathIdentificationString
 * @property-read string $pathString Path string for location (like /1/2/)
 * @property-read int $mainLocationId
 * @property-read int $depth
 * @property-read \ezp\Content\Location[] $children Location's children in subtree
 * @property-read \ezp\Content $content Associated Content object
 * @property-read \ezp\Content\Location $parent Location's parent location
 */
class Location extends Model implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'priority' => true,
        'hidden' => false,
        'invisible' => false,
        'remoteId' => true,
        'pathIdentificationString' => false,
        'pathString' => false,
        'mainLocationId' => false,
        'depth' => false,
        'containerProperties' => true,
        'children' => false
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'content' => false,
        'parent' => false,
        'parentId' => false,
        'contentId' => false,
        'sortField' => true,
        'sortOrder' => true
    );

    /**
     * Container properties
     *
     * @var \ezp\Content\ContainerProperty[]
     */
    protected $containerProperties;

    /**
     * Children of current location
     * @var \ezp\Content\Location[]
     */
    protected $children;

    /**
     * Content for current location
     * @var \ezp\Content|\ezp\Content\Proxy
     */
    protected $content;

    /**
     * Current location's parent
     * @var \ezp\Content\Location|\ezp\Content\Proxy
     */
    protected $parent;

    /**
     * Setups empty children collection and attaches $content
     *
     * @param \ezp\Content|\ezp\Content\Proxy $content
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    public function __construct( $content )
    {
        if ( !$content instanceof Content && !$content instanceof Proxy )
        {
            throw new InvalidArgumentType( '$content', 'ezp\\Content, ezp\\Content\\Proxy', $content );
        }

        $this->properties = new LocationValue;
        $this->containerProperties = new TypeCollection( 'ezp\\Content\\ContainerProperty' );
        $this->children = new TypeCollection( 'ezp\\Content\\Location' );

        // If instantiation is made with concrete Content,
        // do concrete setContent() to fixup dependencies between Content and Location
        if ( $content instanceof Content )
        {
            $this->setContent( $content );
        }
        else
        {
            $this->content = $content;
        }
    }

    /**
     * Returns the parent Location
     *
     * @return \ezp\Content\Location
     */
    protected function getParent()
    {
        if ( $this->parent instanceof Proxy )
        {
            $this->parent = $this->parent->load();
        }
        return $this->parent;
    }

    /**
     * Return the parent Location id
     *
     * @return int
     */
    protected function getParentId()
    {
        if ( $this->parent instanceof Proxy || $this->parent instanceof Location )
        {
            return $this->parent->id;
        }
        return 0;
    }

    /**
     * Sets the parent Location and updates inverse side ( $parent->children )
     *
     * @param \ezp\Content\Location $parent
     */
    protected function setParent( Location $parent )
    {
        $this->parent = $parent;
        $parent->children[] = $this;
    }

    /**
     * Returns the Content the Location holds
     *
     * @return \ezp\Content
     */
    protected function getContent()
    {
        if ( $this->content instanceof Proxy )
        {
            $this->content = $this->content->load();
        }
        return $this->content;
    }

    /**
     * Returns the Content id
     *
     * @return int
     */
    protected function getContentId()
    {
        if ( $this->content instanceof Proxy || $this->content instanceof Content )
        {
            return $this->content->id;
        }
        return 0;
    }

    /**
     * Sets the content and updates inverse side ( $content->locations )
     *
     * @param \ezp\Content $content
     */
    protected function setContent( Content $content )
    {
        $this->content = $content;
        $content->locations[] = $this;
    }

    /**
     * Dynamic property getter
     * Returns sort field for location
     * @return int Sort field int, to be compared with \ezp\Content\ContainerProperty::SORT_FIELD_* constants
     */
    protected function getSortField()
    {
        if ( isset( $this->containerProperties[0] ) )
        {
            return $this->containerProperties[0]->sortField;
        }

        return $this->properties->sortField;
    }

    /**
     * Dynamic property setter
     * Sets sort field for location
     * @param int $sortField One of \ezp\Content\ContainerProperty::SORT_FIELD_* constants
     */
    protected function setSortField( $sortField )
    {
        if ( !isset( $this->containerProperties[0] ) )
        {
            $this->containerProperties[] = new ContainerProperty;
        }

        $this->containerProperties[0]->sortField = $sortField;
        $this->properties->sortField = $sortField;
    }

    /**
     * Dynamic property getter
     * Returns sort order for location
     * @return int Sort order int, to be compared with \ezp\Content\ContainerProperty::SORT_ORDER_* constants
     */
    protected function getSortOrder()
    {
        if ( isset( $this->containerProperties[0] ) )
        {
            return $this->containerProperties[0]->sortOrder;
        }

        return $this->properties->sortOrder;
    }

    /**
     * Dynamic property setter
     * Sets sort order for location
     * @param int $sortOrder One of \ezp\Content\ContainerProperty::SORT_ORDER_* constants
     */
    protected function setSortOrder( $sortOrder )
    {
        if ( !isset( $this->containerProperties[0] ) )
        {
            $this->containerProperties[] = new ContainerProperty;
        }

        $this->containerProperties[0]->sortOrder = $sortOrder;
        $this->properties->sortOrder = $sortOrder;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\Base\Observable $subject
     * @param string $event
     * @return \ezp\Content\Location
     */
    public function update( Observable $subject, $event = 'update' )
    {
        if ( $subject instanceof Content )
        {
            return $this->notify( $event );
        }
        return $this;
    }
}
