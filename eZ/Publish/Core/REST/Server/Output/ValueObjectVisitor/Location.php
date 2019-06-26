<?php

/**
 * File containing the RestLocation ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestContent as RestContentValue;
use eZ\Publish\API\Repository\Values\Content\Location as LocationValue;

/**
 * Location value object visitor.
 */
class Location extends ValueObjectVisitor
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function visit(Visitor $visitor, Generator $generator, $location)
    {
        $generator->startObjectElement('Location');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Location'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('LocationUpdate'));
        $this->visitLocationAttributes($visitor, $generator, $location);
        $generator->endObjectElement('Location');
    }

    protected function visitLocationAttributes(Visitor $visitor, Generator $generator, LocationValue $location)
    {
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

        $generator->startValueElement('childCount', $this->locationService->getLocationChildCount($location));
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
            $this->router->generate('ezpublish_rest_loadContent', ['contentId' => $location->contentId])
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
                ['contentId' => $location->contentId]
            )
        );
        $generator->endAttribute('href');
        $visitor->visitValueObject(new RestContentValue($location->contentInfo));
        $generator->endObjectElement('ContentInfo');
    }
}
