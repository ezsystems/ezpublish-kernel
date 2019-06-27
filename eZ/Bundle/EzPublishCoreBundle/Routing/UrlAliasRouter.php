<?php

/**
 * File containing the UrlAliasRouter class.
 *
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

    /**
     * Will return the right UrlAlias in regards to configured root location.
     *
     * @param string $pathinfo
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the path does not exist or is not valid for the given language
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    protected function getUrlAlias($pathinfo)
    {
        $pathPrefix = $this->generator->getPathPrefixByRootLocationId($this->rootLocationId);

        if (
            $this->rootLocationId === null ||
            $this->generator->isUriPrefixExcluded($pathinfo) ||
            $pathPrefix === '/'
        ) {
            return parent::getUrlAlias($pathinfo);
        }

        return $this->urlAliasService->lookup($pathPrefix . $pathinfo);
    }
}
