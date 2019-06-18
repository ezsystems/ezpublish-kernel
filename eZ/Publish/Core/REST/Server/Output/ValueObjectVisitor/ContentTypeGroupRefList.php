<?php

/**
 * File containing the ContentTypeGroupRefList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ContentTypeGroupRefList value object visitor.
 */
class ContentTypeGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentTypeGroupRefList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeGroupRefList'));

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_listContentTypesForGroup',
                [
                    'contentTypeGroupId' => $data->contentType->id,
                ]
            )
        );
        $generator->endAttribute('href');

        $groupCount = count($data->contentTypeGroups);

        $generator->startList('ContentTypeGroupRef');
        foreach ($data->contentTypeGroups as $contentTypeGroup) {
            $generator->startObjectElement('ContentTypeGroupRef', 'ContentTypeGroup');

            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_loadContentTypeGroup',
                    [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                    ]
                )
            );
            $generator->endAttribute('href');

            // Unlinking last group is not allowed
            if ($groupCount > 1) {
                $generator->startHashElement('unlink');

                $generator->startAttribute(
                    'href',
                    $this->router->generate(
                        'ezpublish_rest_unlinkContentTypeFromGroup',
                        [
                            'contentTypeId' => $data->contentType->id,
                            'contentTypeGroupId' => $contentTypeGroup->id,
                        ]
                    )
                );
                $generator->endAttribute('href');

                $generator->startAttribute('method', 'DELETE');
                $generator->endAttribute('method');

                $generator->endHashElement('unlink');
            }

            $generator->endObjectElement('ContentTypeGroupRef');
        }
        $generator->endList('ContentTypeGroupRef');

        $generator->endObjectElement('ContentTypeGroupRefList');
    }
}
