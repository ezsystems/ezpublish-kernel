<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface;

final class ViewMatcherRegistry
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface[] */
    private $matchers;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface[] $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
    }

    public function setMatcher(string $matcherIdentifier, ViewMatcherInterface $matcher): void
    {
        $this->matchers[$matcherIdentifier] = $matcher;
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ViewMatcherInterface
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getMatcher(string $matcherIdentifier): ViewMatcherInterface
    {
        if (!isset($this->matchers[$matcherIdentifier])) {
            throw new NotFoundException('Matcher', $matcherIdentifier);
        }

        return $this->matchers[$matcherIdentifier];
    }
}
