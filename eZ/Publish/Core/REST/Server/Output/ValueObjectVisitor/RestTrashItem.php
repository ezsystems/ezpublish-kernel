<?php

/**
 * File containing the RestTrashItem ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestContent as RestContentValue;

/**
 * RestTrashItem value object visitor.
 */
class RestTrashItem extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestTrashItem $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('TrashItem');
        $visitor->setHeader('Content-Type', $generator->getMediaType('TrashItem'));

        $trashItem = $data->trashItem;
        $contentInfo = $trashItem->getContentInfo();

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadTrashItem', ['trashItemId' => $trashItem->id])
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $trashItem->id);
        $generator->endValueElement('id');

        $generator->startValueElement('priority', $trashItem->priority);
        $generator->endValueElement('priority');

        $generator->startValueElement(
            'hidden',
            $this->serializeBool($generator, $trashItem->hidden)
        );
        $generator->endValueElement('hidden');

        $generator->startValueElement(
            'invisible',
            $this->serializeBool($generator, $trashItem->invisible)
        );
        $generator->endValueElement('invisible');

        $pathStringParts = explode('/', trim($trashItem->pathString, '/'));
        $pathStringParts = array_slice($pathStringParts, 0, count($pathStringParts) - 1);

        $generator->startObjectElement('ParentLocation', 'Location');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                [
                    'locationPath' => implode('/', $pathStringParts),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ParentLocation');

        $generator->startValueElement('pathString', $trashItem->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement('depth', $trashItem->depth);
        $generator->endValueElement('depth');

        $generator->startValueElement('childCount', $data->childCount);
        $generator->endValueElement('childCount');

        $generator->startValueElement('remoteId', $trashItem->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startObjectElement('Content');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContent', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Content');

        $generator->startValueElement('sortField', $this->serializeSortField($trashItem->sortField));
        $generator->endValueElement('sortField');

        $generator->startValueElement('sortOrder', $this->serializeSortOrder($trashItem->sortOrder));
        $generator->endValueElement('sortOrder');

        $generator->startObjectElement('ContentInfo', 'ContentInfo');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadContent',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endAttribute('href');
        $visitor->visitValueObject(new RestContentValue($contentInfo));
        $generator->endObjectElement('ContentInfo');

        $generator->endObjectElement('TrashItem');
    }
}
