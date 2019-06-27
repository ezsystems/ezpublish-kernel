<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Facilitates injection/access to request stack, and thus access to ongoing request
 * for services that need it.
 */
trait RequestStackAware
{
    /** @var RequestStack */
    private $requestStack;

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
