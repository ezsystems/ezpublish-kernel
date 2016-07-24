<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Output\ValueHrefGeneratorInterface;
use eZ\Publish\Core\REST\Server\ValueLoaders\TypeMapValueLoaderDispatcher;
use eZ\Publish\Core\REST\Server\ValueLoaders\ValueLoaderInterface;

class LoadableValueObjectReference extends ValueObjectVisitor
{
    /**
     * @var ValueHrefGenerator
     */
    private $valueHrefGenerator;

    /**
     * @var ValueReferenceLoader
     */
    private $valueReferenceLoader;

    /**
     * @var ValueLoaderInterface
     */
    private $valueLoader;

    /**
     * @var array
     */
    private $expandedPathList = [
        'Location.ContentInfo',
        'Location.ContentInfo.Content.Owner',
        'Content.Owner',
    ];

    public function __construct(ValueHrefGeneratorInterface $valueHrefGenerator, TypeMapValueLoaderDispatcher $valueLoader)
    {
        $this->valueHrefGenerator = $valueHrefGenerator;
        $this->valueLoader = $valueLoader;
    }

    /**
     * @param Visitor $visitor
     * @param Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\LoadableValueObjectReference $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startAttribute(
            'href',
            $this->valueHrefGenerator->generate($data->type, $data->loadParameters)
        );

        $generator->endAttribute('href');

        if (in_array($generator->getStackPath(), $this->expandedPathList)) {
            if ($valueObject = $this->valueLoader->load($data->type, $data->loadParameters)) {
                $visitor->visitValueObject($valueObject);
            }
        }
    }
}
