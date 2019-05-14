<?php

/**
 * File containing the TemporaryRedirect ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * TemporaryRedirect value object visitor.
 */
class TemporaryRedirect extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\TemporaryRedirect $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $visitor->setStatus(307);
        $visitor->setHeader('Location', $data->redirectUri);
    }
}
