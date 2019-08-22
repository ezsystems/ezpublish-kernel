<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface;

final class ViewMatcherRegistry
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] */
    private $matchers;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface[] $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getMatcher(string $matcherIdentifier): MatcherInterface
    {
        if (!isset($this->matchers[$matcherIdentifier])) {
            throw new NotFoundException('Matcher', $matcherIdentifier);
        }

        return $this->matchers[$matcherIdentifier];
    }
}
