<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

class SiteAccessMatcherRegistryTest extends TestCase
{
    private const MATCHER_NAME = 'test_matcher';

    public function testGetMatcher(): void
    {
        $matcher = $this->getMatcherMock();
        $registry = new SiteAccessMatcherRegistry([self::MATCHER_NAME => $matcher]);

        $this->assertSame($matcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testSetMatcher(): void
    {
        $matcher = $this->getMatcherMock();
        $registry = new SiteAccessMatcherRegistry();

        $registry->setMatcher(self::MATCHER_NAME, $matcher);

        $this->assertSame($matcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testSetMatcherOverride(): void
    {
        $matcher = $this->getMatcherMock();
        $newMatcher = $this->getMatcherMock();
        $registry = new SiteAccessMatcherRegistry([self::MATCHER_NAME, $matcher]);

        $registry->setMatcher(self::MATCHER_NAME, $newMatcher);

        $this->assertSame($newMatcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testGetMatcherNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $registry = new SiteAccessMatcherRegistry();

        $registry->getMatcher(self::MATCHER_NAME);
    }

    protected function getMatcherMock(): Matcher
    {
        return $this->createMock(Matcher::class);
    }
}
