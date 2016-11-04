<?php

/**
 * File containing the Trash class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Trash view model.
 */
class Trash extends RestValue
{
    /**
     * Trash items.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestTrashItem[]
     */
    public $trashItems;

    /**
     * Path used to load the list of the trash items.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestTrashItem[] $trashItems
     * @param string $path
     */
    public function __construct(array $trashItems, $path)
    {
        $this->trashItems = $trashItems;
        $this->path = $path;
    }
}
