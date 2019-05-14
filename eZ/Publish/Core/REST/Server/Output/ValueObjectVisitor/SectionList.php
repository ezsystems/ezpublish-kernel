<?php

/**
 * File containing the SectionList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * SectionList value object visitor.
 */
class SectionList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\SectionList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('SectionList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SectionList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('Section');
        foreach ($data->sections as $section) {
            $visitor->visitValueObject($section);
        }
        $generator->endList('Section');

        $generator->endObjectElement('SectionList');
    }
}
