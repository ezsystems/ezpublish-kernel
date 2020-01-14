<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;

/**
 * A view matcher factory that also accepts services as matchers.
 *
 * If a service id is passed as the MatcherIdentifier, this service will be used for the matching.
 * Otherwise, it will fallback to the class name based matcher factory.
 */
final class ServiceAwareMatcherFactory extends ClassNameMatcherFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Matcher\ViewMatcherRegistry */
    private $viewMatcherRegistry;

    public function __construct(
        ViewMatcherRegistry $viewMatcherRegistry,
        Repository $repository,
        $relativeNamespace = null,
        array $matchConfig = []
    ) {
        $this->viewMatcherRegistry = $viewMatcherRegistry;

        parent::__construct($repository, $relativeNamespace, $matchConfig);
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function getMatcher($matcherIdentifier)
    {
        try {
            return $this->viewMatcherRegistry->getMatcher($matcherIdentifier);
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            return parent::getMatcher($matcherIdentifier);
        }
    }
}
