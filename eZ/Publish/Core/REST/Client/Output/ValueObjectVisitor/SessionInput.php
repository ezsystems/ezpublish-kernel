<?php

/**
 * File containing the SessionInput class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRestCommon\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRestCommon\Output\Generator;
use EzSystems\EzPlatformRestCommon\Output\Visitor;

/**
 * SessionInput value object visitor.
 */
class SessionInput extends ValueObjectVisitor
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
        $generator->startObjectElement('SessionInput');
        $visitor->setHeader('Content-Type', $generator->getMediaType('SessionInput'));

        $generator->startValueElement('login', $data->login);
        $generator->endValueElement('login');

        $generator->startValueElement('password', $data->password);
        $generator->endValueElement('password');

        $generator->endObjectElement('SessionInput');
    }
}
