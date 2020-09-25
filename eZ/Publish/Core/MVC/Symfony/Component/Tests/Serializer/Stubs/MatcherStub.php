<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

final class MatcherStub implements Matcher
{
    /** @var mixed */
    private $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function setRequest(SimplifiedRequest $request)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function match()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getData()
    {
        return $this->data;
    }
}
