<?php

/**
 * File containing the CreatedContentTypeGroup ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * CreatedContentTypeGroup value object visitor.
 *
 * @todo coverage add test
 */
class CreatedContentTypeGroup extends ContentTypeGroup
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedContentTypeGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->contentTypeGroup);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadContentTypeGroup',
                array('contentTypeGroupId' => $data->contentTypeGroup->id)
            )
        );
        $visitor->setStatus(201);
    }
}
