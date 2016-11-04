<?php

/**
 * File containing the ViewInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\API\Repository\Values\Content\LocationQuery as LocationQueryValueObject;
use eZ\Publish\Core\REST\Server\Input\Parser\Query as QueryParser;

/**
 * Parser for LocationQuery.
 */
class LocationQuery extends QueryParser
{
    protected function buildQuery()
    {
        return new LocationQueryValueObject();
    }
}
