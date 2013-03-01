<?php
/**
 * File containing the Service class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

class Block extends Base
{
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

    public function addItem( Item $item )
    {
        $this->properties['items'][] = $item;
    }

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
