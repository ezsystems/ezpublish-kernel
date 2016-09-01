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
use eZ\Publish\Core\REST\Server\ValueLoaders\UriValueLoader;

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
            try {
                $this->visitorDispatcher->visit(
                    $this->valueLoader->load($data->link, $data->mediaType ?: null),
                    new ExpansionGenerator($generator),
                    $visitor
                );
            } catch (ApiUnauthorizedException $e) {
            }
        }
    }
}
