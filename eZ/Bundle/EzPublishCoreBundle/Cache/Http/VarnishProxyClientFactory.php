<?php

/**
 * File containing the VarnishProxyClientFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Http;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Factory for Varnish proxy client.
 *
 * @deprecated since 6.8. Use the VarnishProxyClientFactory from the platform-http-cache package.
 */
class VarnishProxyClientFactory
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var DynamicSettingParserInterface
     */
    private $dynamicSettingParser;

    /**
     * Configured class for Varnish proxy client service.
     *
     * @var string
     */
    private $proxyClientClass;

    public function __construct(
        ConfigResolverInterface $configResolver,
        DynamicSettingParserInterface $dynamicSettingParser,
        $proxyClientClass
    ) {
        $this->configResolver = $configResolver;
        $this->dynamicSettingParser = $dynamicSettingParser;
        $this->proxyClientClass = $proxyClientClass;
    }

    /**
     * Builds the proxy client, taking dynamically defined servers into account.
     *
     * @param array $servers
     * @param string $baseUrl
     *
     * @return \FOS\HttpCache\ProxyClient\Varnish
     */
    public function buildProxyClient(array $servers, $baseUrl)
    {
        $allServers = array();
        foreach ($servers as $server) {
            if (!$this->dynamicSettingParser->isDynamicSetting($server)) {
                $allServers[] = $server;
                continue;
            }

            $settings = $this->dynamicSettingParser->parseDynamicSetting($server);
            $configuredServers = $this->configResolver->getParameter(
                $settings['param'],
                $settings['namespace'],
                $settings['scope']
            );
            $allServers = array_merge($allServers, (array)$configuredServers);
        }

        $class = $this->proxyClientClass;

        return new $class($allServers, $baseUrl);
    }
}
