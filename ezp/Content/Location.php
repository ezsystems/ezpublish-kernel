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
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Observable,
    ezp\Content,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * This class represents a Content Location
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
class Location extends Model
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
            $this->properties->contentId = $content->id;
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
     * Sets the parent Location and updates inverse side ( $parent->children )
     *
     * @param \ezp\Content\Location $parent
     */
    protected function setParent( Location $parent )
    {
        $this->parent = $parent;
        $this->properties->parentId = $parent->id;
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
     * Sets the content and updates inverse side ( $content->locations )
     *
     * @param \ezp\Content $content
     */
    protected function setContent( Content $content )
    {
        $this->content = $content;
        $this->properties->contentId = $content->id;
        $content->locations[] = $this;
    }

    /**
     * Returns collection of children locations
     *
     * @return \ezp\Content\Location[]
     */
    protected function getChildren()
    {
        return $this->children;
    }
}
