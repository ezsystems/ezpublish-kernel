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
    ezp\Base\Interfaces\Observer,
    ezp\Base\TypeCollection,
    ezp\Base\Interfaces\Observable,
    ezp\Content,
    ezp\Persistence\Content\Location as LocationValue,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * This class represents a Content Location
 *
 */
class Location extends Model implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'depth' => false,
        'isHidden' => true,
        'isInvisible' => true,
        'containerProperties' => true,
        'children' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'content' => false,
        'parent' => false,
        //"parentId" => true,
        //"contentId" => true,
    );

    /**
     * Container properties
     *
     * @var ContainerProperty[]
     */
    protected $containerProperties;

    /**
     * Children of current location
     * @var Location[]
     */
    protected $children;

    /**
     * Content for current location
     * @var Content|Proxy
     */
    protected $content;

    /**
     * Setups empty children collection and attaches $content
     *
     * @param Content|Proxy $content
     * @throws \ezp\Base\Exception\InvalidArgumentType
     */
    public function __construct( $content )
    {
        if ( !$content instanceof Content && !$content instanceof Proxy )
        {
            throw new InvalidArgumentType( '$content', 'ezp\\Content, ezp\\Content\\Proxy', $content );
        }

        $this->properties = new LocationValue;
        $this->content = $content;
        $this->containerProperties = new TypeCollection( 'ezp\\Content\\ContainerProperty' );
        $this->children = new TypeCollection( 'ezp\\Content\\Location' );
    }

    /**
     * Returns the parent Location
     *
     * @return Location
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
     * @param Location $parent
     */
    protected function setParent( Location $parent )
    {
        $this->parent = $parent;
        $parent->children[] = $this;
    }

    /**
     * Returns the Content the Location holds
     *
     * @return Content
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
     * @param Content $content
     */
    protected function setContent( Content $content )
    {
        $this->content = $content;
        $content->locations[] = $this;

    }

    /**
     * Called when subject has been updated
     *
     * @param ezp\Base\Observable $subject
     * @param string $event
     * @return Location
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
