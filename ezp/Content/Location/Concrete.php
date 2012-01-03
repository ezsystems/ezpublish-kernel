<?php
/**
 * File containing the ezp\Content\Location\Concrete class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Location;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Content,
    ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Location,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Collection\Lazy;

/**
 * This class represents a Concrete Content Location
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
class Concrete extends Model implements Location
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'priority' => true,
        'hidden' => false,
        'invisible' => false,
        'remoteId' => true,//@todo: Make readOnly
        'contentId' => false,
        'parentId' => false,
        'pathIdentificationString' => false,
        'pathString' => false,
        'modifiedSubLocation' => false,
        'mainLocationId' => false,
        'depth' => false,
        'sortField' => true,
        'sortOrder' => true,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'content' => false,
        'parent' => false,
        'children' => false
    );

    /**
     * Children of current location
     * @var \ezp\Content\Location[]
     */
    protected $children;

    /**
     * Content for current location
     * @var \ezp\Content
     */
    protected $content;

    /**
     * Current location's parent
     * @var \ezp\Content\Location
     */
    protected $parent;

    /**
     * Setups empty children collection and attaches $content
     *
     * @param \ezp\Content $content
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    public function __construct( Content $content )
    {
        $this->properties = new LocationValue;
        $this->children = new TypeCollection( 'ezp\\Content\\Location' );

        // If instantiation is made with concrete Content,
        // do concrete setContent() to fixup dependencies between Content and Location
        if ( $content instanceof ConcreteContent )
        {
            $this->setContent( $content );
        }
        else
        {
            $this->content = $content;
            $this->properties->contentId = $content->id;
        }
    }

    /**
     * Returns the parent Location
     *
     * @return \ezp\Content\Location
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent Location and updates inverse side ( $parent->children ) (if it has been loaded)
     *
     * Note: This function does not store the change, use Location service for such functionality!
     *
     * @todo Should be removed or documented as internal as you will have to use move api if you want to change
     *       parent after creation.
     * @param \ezp\Content\Location $parent
     */
    public function setParent( Location $parent )
    {
        $this->parent = $parent;
        $this->properties->parentId = $parent->id;
        $children = $parent->getChildren();
        if ( !$children instanceof Lazy || $children->isLoaded() )
            $children[] = $this;
    }

    /**
     * Returns the Content the Location holds
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content and updates inverse side ( $content->locations )
     *
     * @param \ezp\Content $content
     */
    public function setContent( Content $content )
    {
        $this->content = $content;
        $this->properties->contentId = $content->id;
        $locations = $content->getLocations();
        $locations[] = $this;
    }

    /**
     * Returns collection of children locations
     *
     * @return \ezp\Content\Location[]
     */
    public function getChildren()
    {
        return $this->children;
    }
}
