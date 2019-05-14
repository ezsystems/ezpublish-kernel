<?php

/**
 * File containing the CreatedObjectState ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * CreatedObjectState value object visitor.
 *
 * @todo coverage add test
 */
class CreatedObjectState extends RestObjectState
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedObjectState $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->objectState);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_loadObjectState',
                array(
                    'objectStateGroupId' => $data->objectState->groupId,
                    'objectStateId' => $data->objectState->objectState->id,
                )
            )
        );
        $visitor->setStatus(201);
    }
}
