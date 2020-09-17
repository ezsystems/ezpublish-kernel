<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Routing;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter as BaseUrlAliasRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class UrlAliasRouter extends BaseUrlAliasRouter
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface; */
    protected $configResolver;

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function matchRequest(Request $request)
    {
        // UrlAliasRouter might be disabled from configuration.
        // An example is for running the admin interface: it needs to be entirely run through the legacy kernel.
        if ($this->configResolver->getParameter('url_alias_router') === false) {
            throw new ResourceNotFoundException('Config says to bypass UrlAliasRouter');
        }

        return parent::matchRequest($request);
    }
}
