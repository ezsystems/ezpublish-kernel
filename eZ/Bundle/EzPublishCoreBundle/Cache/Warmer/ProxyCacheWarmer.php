<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Cache\Warmer;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyGeneratorInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class ProxyCacheWarmer implements CacheWarmerInterface
{
    public const PROXY_CLASSES = [
        Content::class,
        ContentInfo::class,
        ContentType::class,
        ContentTypeGroup::class,
        Language::class,
        Location::class,
        Section::class,
        User::class,
    ];

    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyGeneratorInterface */
    private $proxyGenerator;

    public function __construct(ProxyGeneratorInterface $proxyGenerator)
    {
        $this->proxyGenerator = $proxyGenerator;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDir): void
    {
        $this->proxyGenerator->warmUp(self::PROXY_CLASSES);
    }
}
