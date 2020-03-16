<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * This criterion implements a logical AND criterion and will only match
 * if ALL of the given criteria match.
 */
class LogicalAnd extends LogicalOperator
{
}
