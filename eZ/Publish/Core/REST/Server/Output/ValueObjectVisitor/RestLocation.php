<?php

/**
 * File containing the RestLocation ValueObjectVisitor class.
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
 * RestLocation value object visitor.
 */
class RestLocation extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestLocation $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Location');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Location'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('LocationUpdate'));

        $location = $data->location;
        $contentInfo = $location->getContentInfo();

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                ['locationPath' => trim($location->pathString, '/')]
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $location->id);
        $generator->endValueElement('id');

        $generator->startValueElement('priority', $location->priority);
        $generator->endValueElement('priority');

        $generator->startValueElement(
            'hidden',
            $this->serializeBool($generator, $location->hidden)
        );
        $generator->endValueElement('hidden');

        $generator->startValueElement(
            'invisible',
            $this->serializeBool($generator, $location->invisible)
        );
        $generator->endValueElement('invisible');

        $generator->startObjectElement('ParentLocation', 'Location');
        if (trim($location->pathString, '/') !== '1') {
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_loadLocation',
                    [
                        'locationPath' => implode('/', array_slice($location->path, 0, count($location->path) - 1)),
                    ]
                )
            );
            $generator->endAttribute('href');
        }
        $generator->endObjectElement('ParentLocation');

        $generator->startValueElement('pathString', $location->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement('depth', $location->depth);
        $generator->endValueElement('depth');

        $generator->startValueElement('childCount', $data->childCount);
        $generator->endValueElement('childCount');

        $generator->startValueElement('remoteId', $location->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startObjectElement('Children', 'LocationList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_loadLocationChildren',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Children');

        $generator->startObjectElement('Content');
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadContent', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Content');

        $generator->startValueElement('sortField', $this->serializeSortField($location->sortField));
        $generator->endValueElement('sortField');

        $generator->startValueElement('sortOrder', $this->serializeSortOrder($location->sortOrder));
        $generator->endValueElement('sortOrder');

        $generator->startObjectElement('UrlAliases', 'UrlAliasRefList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_listLocationURLAliases',
                ['locationPath' => trim($location->pathString, '/')]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('UrlAliases');

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

        $generator->endObjectElement('Location');
    }
}
