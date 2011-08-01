<?php
/**
 * File containing the ezp\Content\Location class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\AbstractModel,
    ezp\Base\Observer,
    ezp\Base\TypeCollection,
    ezp\Base\Observable;

/**
 * This class represents a Content Location
 *
 */
class Location extends AbstractModel implements Observer
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
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
     * @var int
     */
    protected $depth;

    /**
     * A custom ID for the Location.
     *
     * @var string
     */
    public $remoteId = '';

    /**
     * The path of the Location in the tree
     *
     * @var string
     */
    public $path = '';

    /**
     * The parent Location
     *
     * @var Proxy|Location
     */
    protected $parent;

    /**
     * The children Location
     *
     * @var Location[]
     */
    protected $children;

    /**
     * The Content the Location holds
     *
     * @var Proxy|Content
     */
    protected $content;

    /**
     * Hidden flag
     *
     * @var bool
     */
    public $hidden = false;

    /**
     * Visible flag
     *
     * @var bool
     */
    public $visible = true;

    /**
     * Priority of the Location
     *
     * @var int
     */
    public $priority = 0;

    /**
     * Id of the location
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Container properties
     *
     * @var ContainerProperty[]
     */
    protected $containerProperties;

    /**
     * Setups empty children collection and attaches $content
     *
     * @param Content $content
     */
    public function __construct( Content $content )
    {
        $this->containerProperties = new TypeCollection( 'ezp\\Content\\ContainerProperty' );
        $this->children = new TypeCollection( 'ezp\\Content\\Location' );
        $this->setContent( $content );
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
?>
