<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Matcher;

use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * A view matcher factory that also accepts services as matchers.
 *
 * If a service id is passed as the MatcherIdentifier, this service will be used for the matching.
 * Otherwise, it will fallback to the class name based matcher factory.
 */
class ServiceAwareMatcherFactory extends ClassNameMatcherFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $matcherIdentifier
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MatcherInterface
     */
    protected function getMatcher($matcherIdentifier)
    {
        if ($this->container->has($matcherIdentifier)) {
            return $this->container->get($matcherIdentifier);
        }

        return parent::getMatcher($matcherIdentifier);
    }
}
