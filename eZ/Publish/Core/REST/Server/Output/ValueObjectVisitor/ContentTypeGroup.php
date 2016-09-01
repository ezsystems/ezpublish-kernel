<?php

/**
 * File containing the ContentTypeGroup ValueObjectVisitor class.
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
use eZ\Publish\Core\REST\Server\Values\ResourceRouteReference;

/**
 * ContentTypeGroup value object visitor.
 */
class ContentTypeGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeGroup');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeGroup'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ContentTypeGroupInput'));

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContentTypeGroup', ['contentTypeGroupId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $data->id);
        $generator->endValueElement('id');

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        $generator->startValueElement('created', $data->creationDate->format('c'));
        $generator->endValueElement('created');

        $generator->startValueElement('modified', $data->modificationDate->format('c'));
        $generator->endValueElement('modified');

        $generator->startObjectElement('Creator', 'User');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadUser', ['userId' => $data->creatorId]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Creator');

        $generator->startObjectElement('Modifier', 'User');
        $visitor->visitValueObject(
            new ResourceRouteReference('ezpublish_rest_loadUser', ['userId' => $data->modifierId]),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Modifier');

        $generator->startObjectElement('ContentTypes', 'ContentTypeInfoList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_listContentTypesForGroup',
                ['contentTypeGroupId' => $data->id],
                'ContentTypeInfoList'
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('ContentTypes');

        $generator->endObjectElement('ContentTypeGroup');
    }
}
