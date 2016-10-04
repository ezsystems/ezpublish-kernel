<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as ApiUnauthorizedException;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitorDispatcher;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Output\PathExpansion\ExpansionGenerator;
use eZ\Publish\Core\REST\Server\Output\PathExpansion\PathExpansionChecker;
use eZ\Publish\Core\REST\Server\Output\PathExpansion\Exceptions\MultipleValueLoadException;
use eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UriValueLoader;
use eZ\Publish\Core\REST\Server\Values;

class ResourceLink extends ValueObjectVisitor
{
    /**
     * @var PathExpansionChecker
     */
    private $pathExpansionChecker;

    /**
     * @var UriValueLoader
     */
    private $valueLoader;

    /**
     * @var ValueObjectVisitorDispatcher
     */
    private $visitorDispatcher;

    public function __construct(
        UriValueLoader $valueLoader,
        PathExpansionChecker $pathExpansionChecker,
        ValueObjectVisitorDispatcher $visitorDispatcher)
    {
        $this->valueLoader = $valueLoader;
        $this->pathExpansionChecker = $pathExpansionChecker;
        $this->visitorDispatcher = $visitorDispatcher;
    }

    /**
     * @param Visitor $visitor
     * @param Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ResourceLink $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startAttribute('href', $data->link);
        $generator->endAttribute('href');

        if ($this->pathExpansionChecker->needsExpansion($generator->getStackPath())) {
            $response = $visitor->getResponse();

            if (!$response->headers->contains('Vary', 'X-eZ-Embed-Value')) {
                $response->setVary('X-eZ-Embed-Value', false);
            }

            try {
                $valueObject = $this->valueLoader->load($data->link, $data->mediaType ?: null);
                if ($valueObject instanceof Values\CachedValue) {
                    $valueObject = $this->processCachedValue($valueObject, $visitor);
                }

                $this->visitorDispatcher->visit(
                    $valueObject,
                    new ExpansionGenerator($generator),
                    $visitor
                );
            } catch (ApiUnauthorizedException $e) {
                $generator->startAttribute('embed-error', $e->getMessage());
                $generator->endAttribute('embed-error');
            } catch (MultipleValueLoadException $e) {
                $generator->startAttribute('embed-error', $e->getMessage());
                $generator->endAttribute('embed-error');
            }
        }
    }

    /**
     * Adds cache tags from the given $cachedValue, and returns the wrapped value object.
     *
     * @param \eZ\Publish\Core\REST\Server\Values\CachedValue $cachedValue
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     *
     * @return object The value object wrapped by the $cachedValue
     */
    private function processCachedValue(Values\CachedValue $cachedValue, Visitor $visitor)
    {
        if (!empty($cachedValue->cacheTags)) {
            $response = $visitor->getResponse();
            $tags = [];
            foreach ($cachedValue->cacheTags as $tag => $values) {
                foreach ((array)$values as $value) {
                    $tagValue = $tag . '-' . $value;

                    if (!$response->headers->contains('xkey', $tagValue)) {
                        $tags[] = $tagValue;
                    }
                }
            }
            $response->headers->set('xkey', $tags, false);
        }

        return $cachedValue->value;
    }
}
