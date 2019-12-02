<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\DefaultScopeConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\GlobalScopeConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\SiteAccessGroupConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\StaticSiteAccessConfigResolver;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

abstract class AbstractParserTestCase extends AbstractExtensionTestCase
{
    /**
     * Asserts a parameter from ConfigResolver has expected value for given scope.
     *
     * @param string $parameterName
     * @param mixed $expectedValue
     * @param string $scope SiteAccess name, group, default or global
     * @param bool $assertSame Set to false if you want to use assertEquals() instead of assertSame()
     */
    protected function assertConfigResolverParameterValue($parameterName, $expectedValue, $scope, $assertSame = true)
    {
        $chainConfigResolver = $this->getConfigResolver();
        $assertMethod = $assertSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expectedValue, $chainConfigResolver->getParameter($parameterName, 'ezsettings', $scope));
    }

    protected function getConfigResolver(): ConfigResolverInterface
    {
        $chainConfigResolver = new ChainConfigResolver();
        $siteAccessProvider = $this->getSiteAccessProviderMock();

        $configResolvers = [
            new DefaultScopeConfigResolver('default'),
            new SiteAccessGroupConfigResolver($siteAccessProvider, 'default'),
            new StaticSiteAccessConfigResolver($siteAccessProvider, 'default'),
            new GlobalScopeConfigResolver('default'),
        ];

        foreach ($configResolvers as $priority => $configResolver) {
            $configResolver->setContainer($this->container);
            $chainConfigResolver->addResolver($configResolver, $priority);
        }

        return $chainConfigResolver;
    }

    protected function getSiteAccessProviderMock(): SiteAccessProviderInterface
    {
        $siteAccessProvider = $this->createMock(SiteAccessProviderInterface::class);
        $siteAccessProvider
            ->method('isDefined')
            ->willReturnMap([
                ['ezdemo_site', true],
                ['fre', true],
                ['fre2', true],
                ['ezdemo_site_admin', true],
            ]);
        $siteAccessProvider
            ->method('getSiteAccess')
            ->willReturnMap([
                ['ezdemo_site', $this->getSiteAccess('ezdemo_site', StaticSiteAccessProvider::class, ['ezdemo_group', 'ezdemo_frontend_group'])],
                ['fre', $this->getSiteAccess('fre', StaticSiteAccessProvider::class, ['ezdemo_group', 'ezdemo_frontend_group'])],
                ['fre2', $this->getSiteAccess('fre', StaticSiteAccessProvider::class, ['ezdemo_group', 'ezdemo_frontend_group'])],
                ['ezdemo_site_admin', $this->getSiteAccess('ezdemo_site_admin', StaticSiteAccessProvider::class, ['ezdemo_group'])],
            ]);

        return $siteAccessProvider;
    }

    /**
     * @param string[] $groupNames
     */
    protected function getSiteAccess(string $name, string $provider, array $groupNames): SiteAccess
    {
        $siteAccess = new SiteAccess($name, SiteAccess::DEFAULT_MATCHING_TYPE, null, $provider);
        $siteAccessGroups = [];
        foreach ($groupNames as $groupName) {
            $siteAccessGroups[] = new SiteAccessGroup($groupName);
        }
        $siteAccess->groups = $siteAccessGroups;

        return $siteAccess;
    }
}
