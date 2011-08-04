<?php
/**
 * File containing the ezp\Content\ContainerProperty class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\Exception\InvalidArgumentType;

/**
 * This class represents a container property
 *
 */
class ContainerProperty extends Model
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

    protected $dynamicProperties = array(
        "location" => false,
    );

    /**
     * Id the Location the ContainerProperty refers to
     *
     * @var int
     */
    public $locationId = 0;

    /**
     * Sort field
     * One of the constants SORT_FIELD_*
     *
     * @var int
     */
    public $sortField = self::SORT_FIELD_NAME;

    /**
     * Sort order
     * One of the constants SORT_ORDER_*
     *
     * @var int
     */
    public $sortOrder = self::SORT_ORDER_ASC;

    /**
     * Location the ContainerProperty refers to
     *
     * @var Location|Proxy
     */
    protected $location;

    public function __construct()
    {

    }

    protected function getLocation()
    {
        if ( $this->location instanceof Proxy )
        {
            $this->location = $this->location->load();
        }
        return $this->location;
    }
}
