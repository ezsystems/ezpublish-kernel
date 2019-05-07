<?php

/**
 * File containing the Trash ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * Trash value object visitor.
 */
class Trash extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\Trash $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Trash');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Trash'));
        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('TrashItem');

        foreach ($data->trashItems as $trashItem) {
            $visitor->visitValueObject($trashItem);
        }

        $generator->endList('TrashItem');
        $generator->endObjectElement('Trash');
    }
}
