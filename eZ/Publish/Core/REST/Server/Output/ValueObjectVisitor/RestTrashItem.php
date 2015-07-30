<?php

/**
 * File containing the RestTrashItem ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

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

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadTrashItem', array('trashItemId' => $data->trashItem->id))
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $data->trashItem->id);
        $generator->endValueElement('id');

        $generator->startValueElement('priority', $data->trashItem->priority);
        $generator->endValueElement('priority');

        $generator->startValueElement(
            'hidden',
            $this->serializeBool($generator, $data->trashItem->hidden)
        );
        $generator->endValueElement('hidden');

        $generator->startValueElement(
            'invisible',
            $this->serializeBool($generator, $data->trashItem->invisible)
        );
        $generator->endValueElement('invisible');

        $pathStringParts = explode('/', trim($data->trashItem->pathString, '/'));
        $pathStringParts = array_slice($pathStringParts, 0, count($pathStringParts) - 1);

        $generator->startObjectElement('ParentLocation', 'Location');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                array(
                    'locationPath' => implode('/', $pathStringParts),
                )
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ParentLocation');

        $generator->startValueElement('pathString', $data->trashItem->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement('depth', $data->trashItem->depth);
        $generator->endValueElement('depth');

        $generator->startValueElement('childCount', $data->childCount);
        $generator->endValueElement('childCount');

        $generator->startValueElement('remoteId', $data->trashItem->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startObjectElement('Content');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContent', array('contentId' => $data->trashItem->contentId))
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Content');

        $generator->startValueElement('sortField', $this->serializeSortField($data->trashItem->sortField));
        $generator->endValueElement('sortField');

        $generator->startValueElement('sortOrder', $this->serializeSortOrder($data->trashItem->sortOrder));
        $generator->endValueElement('sortOrder');

        $generator->endObjectElement('TrashItem');
    }
}
