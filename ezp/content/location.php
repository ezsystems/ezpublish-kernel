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

    public function __construct()
    {
        $this->properties = array(
            "id" => false,
            "path" => "",
            "remoteId" => false,
            "parentId" => false,
            "contentId" => false,
            "hidden" => false,
            "visible" => true,
            "priority" => 0,
        );

        $this->readOnlyProperties = array(
            "id" => true,
        );

        $this->dynamicProperties = array(
            "children" => true,
            "parent" => true,
            "content" => true,
        );
    }

    protected function getParent()
    {
        return Repository::get()->getSubtreeService()->loadLocation( $this->parentId );
    }

    protected function getContent()
    {
        return Repository::get()->getContentService()->loadContent( $this->contentId );
    }

    protected function getChildren()
    {
        return Repository::get()->getSubtreeService()->children( $this );
    }
}
?>
