<?php

/**
 * File containing the RestContent ValueObjectVisitor class.
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
use eZ\Publish\Core\REST\Server\Values\Version as VersionValue;

/**
 * RestContent value object visitor.
 */
class RestContent extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestContent $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restContent = $data;
        $contentInfo = $restContent->contentInfo;
        $contentType = $restContent->contentType;
        $mainLocation = $restContent->mainLocation;
        $currentVersion = $restContent->currentVersion;

        $mediaType = ($restContent->currentVersion === null ? 'ContentInfo' : 'Content');

        $generator->startObjectElement('Content', $mediaType);

        $visitor->setHeader('Content-Type', $generator->getMediaType($mediaType));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ContentUpdate'));

        $generator->startAttribute(
            'href',
            $data->path === null ?
                $this->router->generate('ezpublish_rest_loadContent', ['contentId' => $contentInfo->id]) :
                $data->path
        );
        $generator->endAttribute('href');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');
        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startObjectElement('ContentType');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadContentType',
                ['contentTypeId' => $contentInfo->contentTypeId]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('ContentType');

        $generator->startValueElement('Name', $contentInfo->name);
        $generator->endValueElement('Name');

        $generator->startObjectElement('Versions', 'VersionList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadContentVersions',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('CurrentVersion', 'Version');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_redirectCurrentVersion',
                ['contentId' => $contentInfo->id]
            ),
            $generator,
            $visitor
        );

        // Embed current version, if available
        if ($currentVersion !== null) {
            // @todo EZP-26033: this should use rest resource embedding
            $visitor->visitValueObject(
                new VersionValue(
                    $currentVersion,
                    $contentType,
                    $restContent->relations
                ),
                $generator,
                $visitor
            );
        }

        $generator->endObjectElement('CurrentVersion');

        $generator->startObjectElement('Section');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadSection',
                ['sectionId' => $contentInfo->sectionId]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Section');

        // Main location will not exist if we're visiting the content draft
        if ($data->mainLocation !== null) {
            $generator->startObjectElement('MainLocation', 'Location');
            $visitor->visitValueObject(
                new ResourceRouteReference(
                    'ezpublish_rest_loadLocation',
                    ['locationPath' => trim($mainLocation->pathString, '/')]
                ),
                $generator,
                $visitor
            );
            $generator->endObjectElement('MainLocation');
        }

        $generator->startObjectElement('Locations', 'LocationList');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadLocationsForContent',
                ['contentId' => $contentInfo->id]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_loadUser',
                ['userId' => $contentInfo->ownerId]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('Owner');

        // Modification date will not exist if we're visiting the content draft
        if ($contentInfo->modificationDate !== null) {
            $generator->startValueElement(
                'lastModificationDate',
                $contentInfo->modificationDate->format('c')
            );
            $generator->endValueElement('lastModificationDate');
        }

        // Published date will not exist if we're visiting the content draft
        if ($contentInfo->publishedDate !== null) {
            $generator->startValueElement(
                'publishedDate',
                ($contentInfo->publishedDate !== null
                    ? $contentInfo->publishedDate->format('c')
                    : null)
            );
            $generator->endValueElement('publishedDate');
        }

        $generator->startValueElement(
            'mainLanguageCode',
            $contentInfo->mainLanguageCode
        );
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement(
            'currentVersionNo',
            $contentInfo->currentVersionNo
        );
        $generator->endValueElement('currentVersionNo');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $contentInfo->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $generator->startObjectElement('ObjectStates', 'ContentObjectStates');
        $visitor->visitValueObject(
            new ResourceRouteReference(
                'ezpublish_rest_getObjectStatesForContent',
                ['contentId' => $contentInfo->id]
            ),
            $generator,
            $visitor
        );
        $generator->endObjectElement('ObjectStates');

        $generator->endObjectElement('Content');
    }
}
