<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

/**
 * Struct that stores extra target informations for a RandomTarget object.
 */
class RandomTarget extends Target
{
    /**
     * @var mixed depends on storage implementation.
     */
    public $seed;

    public function __construct($seed)
    {
        $this->seed = $seed;
    }
}
