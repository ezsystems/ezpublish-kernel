<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class DeletedUserSessionTest extends ValueObjectVisitorBaseTest
{
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $generatedResponse = new Response();
        $responseHeaders = [
            'foo' => 'bar',
            'some' => 'thing',
        ];
        $cookie = new Cookie('cookie_name', 'cookie_value');
        $generatedResponse->headers->add($responseHeaders);
        $generatedResponse->headers->setCookie($cookie);
        $deletedSessionValue = new Values\DeletedUserSession($generatedResponse);

        $outputVisitor = $this->getVisitorMock();
        $outputVisitor->expects($this->once())
            ->method('setStatus')
            ->with($this->equalTo(204));

        $visitor->visit(
            $outputVisitor,
            $generator,
            $deletedSessionValue
        );

        $this->assertTrue($generator->isEmpty());
        $this->assertSame('bar', $this->getResponseMock()->headers->get('foo'));
        $this->assertSame('thing', $this->getResponseMock()->headers->get('some'));
        $this->assertSame([$cookie], $this->getResponseMock()->headers->getCookies());
    }

    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\DeletedUserSession();
    }
}
