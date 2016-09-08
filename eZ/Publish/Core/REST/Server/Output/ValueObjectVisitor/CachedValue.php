<?php

/**
 * File containing the ContentList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\RequestStackAware;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CachedValue value object visitor.
 */
class CachedValue extends ValueObjectVisitor
{
    use RequestStackAware;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param Visitor   $visitor
     * @param Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CachedValue $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $visitor->visitValueObject($data->value);

        if ($this->getParameter('content.view_cache') !== true) {
            return;
        }

        $response = $visitor->getResponse();
        $response->setPublic();
        $response->setVary('Accept');

        if ($this->getParameter('content.ttl_cache') === true) {
            $response->setSharedMaxAge($this->getParameter('content.default_ttl'));
            $request = $this->getCurrentRequest();
            if (isset($request) && $request->headers->has('X-User-Hash')) {
                $response->setVary('X-User-Hash', false);
            }
        }

        if (!empty($data->cacheTags)) {
            // See doc/specifications/cache/multi_tagging.md
            $tags = [];
            foreach ($data->cacheTags as $tag => $values) {
                foreach ((array)$values as $value) {
                    $tags[] = $tag . '-' . $value;
                }
            }

            $response->headers->set(
                'xkey',
                $tags,
                false
            );
        }
    }

    public function getParameter($parameterName, $defaultValue = null)
    {
        if ($this->configResolver->hasParameter($parameterName)) {
            return $this->configResolver->getParameter($parameterName);
        }

        return $defaultValue;
    }
}
