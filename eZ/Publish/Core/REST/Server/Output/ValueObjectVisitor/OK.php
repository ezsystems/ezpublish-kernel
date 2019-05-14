<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Visitor;

class OK extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $visitor->setStatus(200);
    }
}
