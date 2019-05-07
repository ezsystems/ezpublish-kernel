<?php

/**
 * File containing the ContentTypeGroupCreateStruct visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * ContentTypeGroupCreateStruct value object visitor.
 */
class ContentTypeGroupCreateStruct extends ValueObjectVisitor
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
        $generator->startObjectElement('ContentTypeGroupInput');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentTypeGroupInput'));

        $generator->startValueElement('identifier', $data->identifier);
        $generator->endValueElement('identifier');

        if ($data->creatorId !== null) {
            $generator->startObjectElement('User');
            $generator->startAttribute('href', $data->creatorId);
            $generator->endAttribute('href');
            $generator->endObjectElement('User');
        }

        if ($data->creationDate !== null) {
            $generator->startValueElement('modificationDate', $data->creationDate->format('c'));
            $generator->endValueElement('modificationDate');
        }

        $generator->endObjectElement('ContentTypeGroupInput');
    }
}
