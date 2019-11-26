<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Psr\Log\LoggerInterface;

class RouterPortHostURITest extends RouterBaseTest
{
    public function matchProvider(): array
    {
        return [
            [SimplifiedRequest::fromUrl('http://example.com'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com//'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com//'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:8080/'), 'default_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_siteaccess/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_siteaccess'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/?first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_salt'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa.foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/test/foo/bar/first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/default_sa'), 'fifth_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/first_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa//'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa///test'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/first_sa/foo/bar'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/first_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://third_siteaccess/first_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('https://first_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://first_sa:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess/foo/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/foo/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/foo/'), 'first_sa'],

            [SimplifiedRequest::fromUrl('http://example.com/second_sa'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa?param1=foo'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com/second_sa/foo/'), 'fifth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:82/second_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:83/second_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:82/second_sa/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:83/second_sa/'), 'first_sa'],

            [SimplifiedRequest::fromUrl('http://first_sa:123/second_sa'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://first_siteaccess:123/second_sa/'), 'first_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/foo/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa?param1=foo'), 'second_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:123/second_sa/foo/'), 'second_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:81/'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo'), 'third_sa'],
            [SimplifiedRequest::fromUrl('http://example.com:81/foo/bar'), 'third_sa'],

            [SimplifiedRequest::fromUrl('http://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/'), 'fourth_sa'],
            [SimplifiedRequest::fromUrl('https://example.com:82/foo'), 'fourth_sa'],
        ];
    }

    protected function createRouter(): Router
    {
        return new Router(
            $this->matcherBuilder,
            $this->createMock(LoggerInterface::class),
            'default_sa',
            [
                'Map\\Port' => [
                    80 => 'fifth_sa',
                    81 => 'third_sa',
                    82 => 'fourth_sa',
                    83 => 'first_sa',
                    85 => 'first_sa',
                    443 => 'fourth_sa',
                ],
                'Map\\Host' => [
                    'first_sa' => 'first_sa',
                    'first_siteaccess' => 'first_sa',
                    'third_siteaccess' => 'third_sa',
                ],
                'Map\\URI' => [
                    'first_sa' => 'first_sa',
                    'second_sa' => 'second_sa',
                ],
            ],
            $this->siteAccessProvider
        );
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests\SiteAccessSetting[]
     */
    public function getSiteAccessProviderSettings(): array
    {
        return [
            new SiteAccessSetting('first_sa', true),
            new SiteAccessSetting('second_sa', true),
            new SiteAccessSetting('third_sa', true),
            new SiteAccessSetting('fourth_sa', true),
            new SiteAccessSetting('fifth_sa', true),
        ];
    }
}
