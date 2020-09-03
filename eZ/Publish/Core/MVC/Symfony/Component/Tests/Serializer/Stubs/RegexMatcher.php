<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex as BaseRegex;

final class RegexMatcher extends BaseRegex
{
    public function getName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}