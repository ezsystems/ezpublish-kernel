<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

/**
 * Struct that stores extra target informations for a RandomTarget object.
 */
class RandomTarget extends Target
{
    /**
     * @var int|null
     *
     * For storage which does not support seed in this type,
     * it should be normalized to proper value inside storage implementation.
     */
    public $seed;

    public function __construct(?int $seed)
    {
        $this->seed = $seed;
    }
}
