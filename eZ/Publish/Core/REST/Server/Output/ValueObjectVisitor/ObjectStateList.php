<?php

/**
 * File containing the ObjectStateList ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;
use EzSystems\EzPlatformRestCommon\Values\RestObjectState as RestObjectStateValue;

/**
 * ObjectStateList value object visitor.
 */
class ObjectStateList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ObjectStateList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ObjectStateList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ObjectStateList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadObjectStates', array('objectStateGroupId' => $data->groupId))
        );

        $generator->endAttribute('href');

        $generator->startList('ObjectState');
        foreach ($data->states as $state) {
            $visitor->visitValueObject(
                new RestObjectStateValue($state, $data->groupId)
            );
        }
        $generator->endList('ObjectState');

        $generator->endObjectElement('ObjectStateList');
    }
}
