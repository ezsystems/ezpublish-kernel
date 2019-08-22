<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface;
use PHPUnit\Framework\TestCase;

class MatcherServiceRegistryTest extends TestCase
{
    private const MATCHER_NAME = 'test_matcher';

    public function testGetMatcher(): void
    {
        $matcher = $this->getMatcherMock();
        $registry = new MatcherServiceRegistry([self::MATCHER_NAME => $matcher]);

        $this->assertSame($matcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testSetMatcher(): void
    {
        $matcher = $this->getMatcherMock();
        $registry = new MatcherServiceRegistry();

        $registry->setMatcher(self::MATCHER_NAME, $matcher);

        $this->assertSame($matcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testSetMatcherOverride(): void
    {
        $matcher = $this->getMatcherMock();
        $newMatcher = $this->getMatcherMock();
        $registry = new MatcherServiceRegistry([self::MATCHER_NAME, $matcher]);

        $registry->setMatcher(self::MATCHER_NAME, $newMatcher);

        $this->assertSame($newMatcher, $registry->getMatcher(self::MATCHER_NAME));
    }

    public function testGetMatcherNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $registry = new MatcherServiceRegistry();

        $registry->getMatcher(self::MATCHER_NAME);
    }

    protected function getMatcherMock(): MatcherInterface
    {
        return $this->createMock(MatcherInterface::class);
    }
}
