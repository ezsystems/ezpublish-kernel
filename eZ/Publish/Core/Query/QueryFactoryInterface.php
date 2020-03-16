<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Query;

use eZ\Publish\API\Repository\Values\Content\Query;

interface QueryFactoryInterface
{
    public function create(string $type, array $parameters = []): Query;
}
