<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface;

class MatcherServiceRegistry
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] */
    private $matchers;

    /**
     * MatcherServiceRegistry constructor.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] $matchers
     */
    public function setMatchers(array $matchers): void
    {
        $this->matchers = $matchers;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    public function hasMatcher(string $matcherIentifier): bool
    {
        return isset($this->matchers[$matcherIentifier]);
    }

    public function setMatcher(string $matcherIdentifier, MatcherInterface $matcher): void
    {
        $this->matchers[$matcherIdentifier] = $matcher;
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function getMatcher(string $matcherIdentifier): MatcherInterface
    {
        if (!isset($this->matchers[$matcherIdentifier])) {
            throw new NotFoundException('Matcher', $matcherIdentifier);
        }

        return $this->matchers[$matcherIdentifier];
    }
}
