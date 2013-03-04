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
 * @property-read string $action Action to be executed. Can be either "add", "modify" or "remove" (see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants)
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
    protected $customAttributes = array();

    /**
     * @see \eZ\Publish\Core\FieldType\Page\Parts\Base for ACTION_* constants
     *
     * @var string
     */
    protected $action;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    protected $items = array();

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Item[]|null
     */
    private $validItems;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Item[]|null
     */
    private $waitingItems;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\Parts\Item[]|null
     */
    private $archivedItems;

    /**
     * Returns valid items (that are to be displayed), for current block.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getValidItems()
    {
        if ( !isset( $this->validItems ) )
            $this->validItems = $this->pageService->getValidBlockItems( $this );

        return $this->validItems;
    }

    /**
     * Returns queued items (the next to be displayed), for current block.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getWaitingItems()
    {
        if ( !isset( $this->waitingItems ) )
            $this->waitingItems = $this->pageService->getWaitingBlockItems( $this );

        return $this->waitingItems;
    }

    /**
     * Returns archived items (that were previously displayed), for current block.
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Item[]
     */
    public function getArchivedItems()
    {
        if ( !isset( $this->archivedItems ) )
            $this->archivedItems = $this->pageService->getArchivedBlockItems( $this );

        return $this->archivedItems;
    }
}
