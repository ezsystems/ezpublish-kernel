<?php

/**
 * File containing the CreatedSection ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * CreatedSection value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedSection extends Section
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedSection $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->section);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadSection',
                array('sectionId' => $data->section->id)
            )
        );
        $visitor->setStatus(201);
    }
}
