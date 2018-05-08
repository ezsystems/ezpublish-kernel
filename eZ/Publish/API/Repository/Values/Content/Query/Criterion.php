<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher\Matcher;

/**
 * @deprecated Deprecated since 7.2, will be removed in 8.0. Use the Matcher instead.
 */
abstract class Criterion extends Matcher
{
}
