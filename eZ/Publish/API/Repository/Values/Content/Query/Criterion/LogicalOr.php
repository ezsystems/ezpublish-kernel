<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * This criterion implements a logical OR criterion and will only match
 * if AT LEAST ONE of the given criteria match.
 */
class LogicalOr extends LogicalOperator
{
}
