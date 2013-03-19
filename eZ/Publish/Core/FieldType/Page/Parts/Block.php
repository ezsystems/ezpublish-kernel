<?php
/**
 * File containing the Page Block class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

/**
 * @property-read string $id Block Id.
 * @property-read string $name Block name.
 * @property-read string $type Block type.
 * @property-read string $view Block view.
 * @property-read string $overflowId Block overflow Id.
 * @property-read array $customAttributes Arbitrary custom attributes (when block is "special").
 * @property-read \eZ\Publish\Core\FieldType\Page\Parts\Item[] $items Block items.
 * @property-read string $action Action to be executed. Can be either "add", "modify" or "remove" (see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants).
 * @property-read array $rotation
 * @property-read string $zoneId Id of zone current block belongs to.
 */
class Block extends Base
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var string
     */
    protected $overflowId;

    /**
     * @var array
     */
    protected $customAttributes;

    /**
     * @see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants
     *
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $rotation;

    /**
     * @var string
     */
    protected $zoneId;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    protected $items = array();
}
