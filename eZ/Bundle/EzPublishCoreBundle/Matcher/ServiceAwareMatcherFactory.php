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
class ServiceAwareMatcherFactory extends ClassNameMatcherFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Matcher\MatcherServiceRegistry */
    private $matcherServiceRegistry;

    public function __construct(
        MatcherServiceRegistry $matcherServiceRegistry,
        Repository $repository,
        $relativeNamespace = null,
        array $matchConfig = []
    ) {
        $this->matcherServiceRegistry = $matcherServiceRegistry;

        parent::__construct($repository, $relativeNamespace, $matchConfig);
    }

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     */
    protected function getMatcher($matcherIdentifier)
    {
        if (strpos($matcherIdentifier, '@') === 0) {
            return $this->matcherServiceRegistry->getMatcher(substr($matcherIdentifier, 1));
        }

        return parent::getMatcher($matcherIdentifier);
    }
}
