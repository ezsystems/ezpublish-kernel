<?php

/**
 * File containing the LocationMatcherFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory as BaseMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @deprecated Deprecated since 6.0, will be removed in 6.1. Location view in general is deprecated. Use content view instead.
 */
class LocationMatcherFactory extends BaseMatcherFactory implements SiteAccessAware, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface; */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver, Repository $repository)
    {
        @trigger_error(
            "Location view has been deprecated in 6.0, and will be removed in 6.1.\n" .
            'Use content view instead.',
            E_USER_DEPRECATED
        );

        $this->configResolver = $configResolver;
        parent::__construct(
            $repository,
            $this->configResolver->getParameter('location_view')
        );
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     */
    protected function getMatcher($matcherIdentifier)
    {
        if ($this->container->has($matcherIdentifier)) {
            return $this->container->get($matcherIdentifier);
        }

        return parent::getMatcher($matcherIdentifier);
    }

    /**
     * Changes internal configuration to use the one for passed SiteAccess.
     *
     * @param SiteAccess $siteAccess
     */
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        if ($siteAccess === null) {
            return;
        }

        $this->matchConfig = $this->configResolver->getParameter('location_view', 'ezsettings', $siteAccess->name);
    }
}
