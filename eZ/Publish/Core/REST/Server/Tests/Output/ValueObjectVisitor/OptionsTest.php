<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values;

class OptionsTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the NoContent visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $noContent = new Values\Options(['GET', 'POST']);

        $this->getVisitorMock()->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(200));

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('setHeader')
            ->will(
                $this->returnValueMap(
                    ['Allow', 'GET,POST'],
                    ['Content-Length', 0]
                )
            );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $noContent
        );
    }

    /**
     * Get the NoContent visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\NoContent
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Options();
    }
}
