<?php

/**
 * File containing the RoleCreateStruct ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * RoleCopyStruct value object visitor.
 */
class RoleCopyStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param mixed $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('RoleInput');
        $visitor->setHeader('Content-Type', $generator->getMediaType('RoleInput'));

        $generator->startValueElement('newIdentifier', $data->newIdentifier);
        $generator->endValueElement('newIdentifier');

        $generator->endObjectElement('RoleInput');
    }
}
