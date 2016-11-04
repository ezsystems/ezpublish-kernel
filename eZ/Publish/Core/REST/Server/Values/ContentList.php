<?php

/**
 * File containing the ContentList class.
 *
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
     * Construct.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\RestContent[] $contents
     */
    public function __construct(array $contents)
    {
        $this->contents = $contents;
    }
}
