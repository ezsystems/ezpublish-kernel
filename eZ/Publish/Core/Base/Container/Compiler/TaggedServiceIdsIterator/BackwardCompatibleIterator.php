<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Container\Compiler\TaggedServiceIdsIterator;

use Iterator;
use IteratorAggregate;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

/**
 * @internal
 */
final class BackwardCompatibleIterator implements IteratorAggregate
{
    /** @var \Symfony\Component\DependencyInjection\TaggedContainerInterface */
    private $container;

    /** @var string */
    private $serviceTag;

    /** @var string */
    private $deprecatedServiceTag;

    public function __construct(TaggedContainerInterface $container, string $serviceTag, string $deprecatedServiceTag)
    {
        $this->container = $container;
        $this->serviceTag = $serviceTag;
        $this->deprecatedServiceTag = $deprecatedServiceTag;
    }

    public function getIterator(): Iterator
    {
        $serviceIdsWithDeprecatedTags = $this->container->findTaggedServiceIds($this->deprecatedServiceTag);
        foreach ($serviceIdsWithDeprecatedTags as $serviceId => $tags) {
            @trigger_error(
                sprintf(
                    'Service tag `%s` is deprecated and will be removed in eZ Platform 4.0. Tag %s with `%s` instead.',
                    $this->deprecatedServiceTag,
                    $serviceId,
                    $this->serviceTag
                ),
                E_USER_DEPRECATED
            );

            yield $serviceId => $tags;
        }

        $taggedServiceIds = $this->container->findTaggedServiceIds($this->serviceTag);
        foreach ($taggedServiceIds as $serviceId => $tags) {
            yield $serviceId => $tags;
        }

        yield from [];
    }
}
