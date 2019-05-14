<?php

/**
 * File containing the SeeOther ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

/**
 * SeeOther Value object visitor.
 */
class SeeOther extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $visitor->setStatus(303);
        $visitor->setHeader('Location', $data->redirectUri);
    }
}
