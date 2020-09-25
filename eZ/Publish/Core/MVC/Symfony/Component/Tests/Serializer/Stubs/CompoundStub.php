<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound;

final class CompoundStub extends Compound
{
    public function __construct(array $subMatchers)
    {
        parent::__construct([]);
        $this->subMatchers = $subMatchers;
    }

    public function match()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function reverseMatch($siteAccessName)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
