<?php

/**
 * File containing the PolicyCreateStruct ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * PolicyCreateStruct value object visitor.
 */
class PolicyCreateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRestCommon\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRestCommon\Output\Generator $generator
     * @param mixed $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('PolicyCreate');
        $visitor->setHeader('Content-Type', $generator->getMediaType('PolicyCreate'));

        $generator->startValueElement('module', $data->module);
        $generator->endValueElement('module');

        $generator->startValueElement('function', $data->function);
        $generator->endValueElement('function');

        $limitations = $data->getLimitations();
        if (!empty($limitations)) {
            $generator->startObjectElement('limitations');
            $generator->startList('limitations');

            foreach ($limitations as $limitation) {
                $visitor->visitValueObject($limitation);
            }

            $generator->endList('limitations');
            $generator->endObjectElement('limitations');
        }

        $generator->endObjectElement('PolicyCreate');
    }
}
