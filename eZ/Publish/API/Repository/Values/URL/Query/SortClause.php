<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\URL\Query;

use InvalidArgumentException;

/**
 * This class is the base for SortClause classes, used to set sorting of URL queries.
 */
abstract class SortClause
{
    const SORT_ASC = 'ascending';
    const SORT_DESC = 'descending';

    /**
     * Sort direction.
     *
     * @var string
     */
    public $direction = self::SORT_ASC;

    /**
     * Sort target.
     *
     * @var string
     */
    public $target;

    /**
     * Constructs a new SortClause on $sortTarget in direction $sortDirection.
     *
     * @param string $sortTarget
     * @param string $sortDirection one of SortClause::SORT_ASC or SortClause::SORT_DESC
     *
     * @throws InvalidArgumentException if the given sort order isn't one of SortClause::SORT_ASC or SortClause::SORT_DESC
     */
    public function __construct(string $sortTarget, string $sortDirection)
    {
        if ($sortDirection !== self::SORT_ASC && $sortDirection !== self::SORT_DESC) {
            throw new InvalidArgumentException('Sort direction must be either SortClause::SORT_ASC or SortClause::SORT_DESC');
        }

        $this->direction = $sortDirection;
        $this->target = $sortTarget;
    }
}
