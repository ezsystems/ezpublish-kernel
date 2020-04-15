<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Content list view model.
 */
class ContentList extends RestValue
{
    /**
     * Contents.
     *
     * @var \eZ\Publish\Core\REST\Server\Values\RestContent[]
     */
    public $contents;

    /**
     * Total items list count.
     *
     * @var int|null
     */
    public $totalCount;

    /**
     * Construct.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestContent[] $contents
     * @param int|null $totalCount
     */
    public function __construct(array $contents, ?int $totalCount = null)
    {
        $this->contents = $contents;
        $this->totalCount = $totalCount;
    }
}
