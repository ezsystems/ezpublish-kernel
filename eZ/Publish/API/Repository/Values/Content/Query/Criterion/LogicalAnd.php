<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\SPI\Repository\Values\Trash\Query\Criterion as TrashCriterion;

/**
 * This criterion implements a logical AND criterion and will only match
 * if ALL of the given criteria match.
 */
class LogicalAnd extends LogicalOperator implements TrashCriterion
{
}
