<?php
/**
 * File containing the ezp\Content\ContainerProperty class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a container property
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class ContainerProperty extends Base implements \ezp\DomainObjectInterface
{
    // TODO const from eZ Publish 4.5
    // needs to update to reflect concept changes
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
    
    public function __construct()
    {
        $this->properties = array(
            "id" => false,
            "locationId" => false,
            "sortField" => false,
            "sortOrder" => false,
        );

        $this->readOnlyProperties = array(
            "id" => true,
        );

        $this->dynamicProperties = array(
            "location" => true,
        );
    }

    protected function getLocation()
    {
        return Repository::get()->getSubtreeService()->load( $this->properties['locationId'] );
    }
}

?>
