<?php

/**
 * File containing the RestTrashItem class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestTrashItem view model.
 */
class RestTrashItem extends RestValue
{
    /**
     * A trash item.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public $trashItem;

    /**
     * Number of children of the trash item.
     *
     * @var int
     */
    public $childCount;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param int $childCount
     */
    public function __construct(TrashItem $trashItem, $childCount)
    {
        $this->trashItem = $trashItem;
        $this->childCount = $childCount;
    }
}
