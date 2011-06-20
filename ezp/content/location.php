<?php
/**
 * File containing the ezp\Content\Location class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content Location
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class Location extends Base implements \ezp\DomainObjectInterface
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
     * Id of the parent Location
     *
     * @var int
     */
    public $parentId = 0;

    /**
     * Id of the content
     *
     * @var int
     */
    public $contentId = 0;

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

    public function __construct()
    {
        $this->properties = array(
            "id" => false,
            "containerProperties" => new ContainerPropertyCollection(),
        );

        $this->dynamicProperties = array(
            "children" => true,
            "parent" => true,
            "content" => true,
        );
    }

    protected function getParent()
    {
        return Repository::get()->getSubtreeService()->load( $this->parentId );
    }

    protected function setParent( Location $parent )
    {
        $this->parentId = $parent->id;
    }

    protected function getContent()
    {
        return Repository::get()->getContentService()->load( $this->contentId );
    }

    protected function getChildren()
    {
        return Repository::get()->getSubtreeService()->children( $this );
    }
}
?>
