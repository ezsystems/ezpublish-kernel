<?php
/**
 * File containing the ezp\content\Location class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Content Location
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Location extends \ezp\base\AbstractModel
{
    /**
     * A custom ID for the Location.
     *
     * @var string
     */
    public $remoteId = "";

    /**
     * The path of the Location in the tree
     *
     * @var string
     */
    public $path = "";

    /**
     * The parent Location
     *
     * @var Proxy|Location
     */
    protected $parent;

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
     * @var ContainerPropertyCollection
     */
    protected $containerProperties;

    public function __construct()
    {
        $this->containerProperties = new ContainerPropertyCollection();

        $this->readableProperties = array(
            "id" => true,
            "containerProperties" => true;
        );

        $this->dynamicProperties = array(
            "children" => true,
            "parent" => true,
            "parentId" => true,
            "content" => true,
            "contentId" => true,
        );

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
        if ( $this->parent instanceof Base )
        {
            return $this->parent->id;
        }
        return 0;
    }

    /**
     * Sets the parent Location
     *
     * @param Proxy|Location $parent
     * @throws \InvalidArgumentException if $content is not an instance of the
     *                                   right class
     */
    protected function setParent( $parent )
    {
        if ( !$parent instanceof Location && !$parent instanceof Proxy )
        {
            throw new \InvalidArgumentException( "Parameter needs to be an instance of Content or Proxy class" );
        }
        else
        {
            $this->parent = $parent;
        }
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
        if ( $this->content instanceof Base )
        {
            return $this->content->id;
        }
        return 0;
    }

    /**
     * Sets the content
     *
     * @param Proxy|Content $content
     * @throws \InvalidArgumentException if $content is not an instance of the
     *                                   right class
     */
    protected function setContent( $content )
    {
        if ( !$content instanceof Content && !$content instanceof Proxy )
        {
            throw new \InvalidArgumentException( "Parameter needs to be an instance of Content or Proxy class" );
        }
        else
        {
            $this->content = $content;
        }
    }

    protected function getChildren()
    {
        // TODO avoid Repository/Service use here with a Lazy Loaded Collection
        return Repository::get()->getSubtreeService()->children( $this );
    }
}
?>
