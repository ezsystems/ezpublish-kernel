<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ResourceLink;
use eZ\Publish\Core\REST\Server\Output\PathExpansion\Exceptions\MultipleValueLoadException;
use eZ\Publish\Core\REST\Server\Values\ResourceLink as ResourceLinkValue;

class ResourceLinkTest extends ValueObjectVisitorBaseTest
{
    private $valueLoaderMock;
    private $pathExpansionCheckerMock;
    private $valueObjectVisitorDispatcherMock;

    /**
     * @param ResourceLinkValue $resourceLink
     * @dataProvider buildValueObject
     */
    public function testVisitWithExpansion(ResourceLinkValue $resourceLink)
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);
        $generator->startObjectElement('SomeRoot');
        $generator->startObjectElement('SomeObject');

        $this->getPathExpansionCheckerMock()
            ->expects($this->once())
            ->method('needsExpansion')
            ->with('SomeRoot.SomeObject')
            ->will($this->returnValue(true));

        $this->getValueLoaderMock()
            ->expects($this->once())
            ->method('load')
            ->with($resourceLink->link, $resourceLink->mediaType)
            ->will($this->returnValue($loadedValue = new \stdClass()));

        $this->getValueObjectVisitorDispatcherMock()
            ->expects($this->once())
            ->method('visit')
            ->with(
                $loadedValue,
                $this->isInstanceOf('eZ\Publish\Core\REST\Server\Output\PathExpansion\ExpansionGenerator'),
                $this->getVisitorMock()
            );

        $visitor->visit($this->getVisitorMock(), $generator, $resourceLink);

        $generator->endObjectElement('SomeObject');
        $generator->endObjectElement('SomeRoot');

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath($dom, '//SomeObject[@href="' . $resourceLink->link . '"]');
        $this->assertXPath($dom, '//SomeObject[@media-type="application/vnd.ez.api.SomeObject+xml"]');
    }

    /**
     * @param ResourceLinkValue $resourceLink
     * @dataProvider buildValueObject
     */
    public function testVisitWithNoExpansion(ResourceLinkValue $resourceLink)
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);
        $generator->startObjectElement('SomeRoot');
        $generator->startObjectElement('SomeObject');

        $this->getPathExpansionCheckerMock()
            ->expects($this->once())
            ->method('needsExpansion')
            ->with('SomeRoot.SomeObject')
            ->will($this->returnValue(false));

        $this->getValueLoaderMock()
            ->expects($this->never())
            ->method('load');

        $this->getValueObjectVisitorDispatcherMock()
            ->expects($this->never())
            ->method('visit');

        $visitor->visit($this->getVisitorMock(), $generator, $resourceLink);

        $generator->endObjectElement('SomeObject');
        $generator->endObjectElement('SomeRoot');

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath($dom, '//SomeObject[@href="' . $resourceLink->link . '"]');
        $this->assertXPath($dom, '//SomeObject[@media-type="application/vnd.ez.api.SomeObject+xml"]');
    }

    /**
     * @dataProvider buildValueObject
     */
    public function testVisitUnauthorizedException(ResourceLinkValue $resourceLink)
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);
        $generator->startObjectElement('SomeRoot');
        $generator->startObjectElement('SomeObject');

        $this->getPathExpansionCheckerMock()
            ->expects($this->once())
            ->method('needsExpansion')
            ->with('SomeRoot.SomeObject')
            ->will($this->returnValue(true));

        $this->getValueLoaderMock()
            ->expects($this->once())
            ->method('load')
            ->will($this->throwException(new UnauthorizedException('something', 'load')));

        $this->getValueObjectVisitorDispatcherMock()
            ->expects($this->never())
            ->method('visit');

        $visitor->visit($this->getVisitorMock(), $generator, $resourceLink);

        $generator->endObjectElement('SomeObject');
        $generator->endObjectElement('SomeRoot');

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath($dom, '//SomeObject[@href="' . $resourceLink->link . '"]');
        $this->assertXPath($dom, '//SomeObject[@media-type="application/vnd.ez.api.SomeObject+xml"]');
        $this->assertXPath($dom, '//SomeObject[@embed-error="User does not have access to \'load\' \'something\'"]');
    }

    /**
     * @dataProvider buildValueObject
     */
    public function testVisitMultipleLoadException(ResourceLinkValue $resourceLink)
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);
        $generator->startObjectElement('SomeRoot');
        $generator->startObjectElement('SomeObject');

        $this->getPathExpansionCheckerMock()
            ->expects($this->once())
            ->method('needsExpansion')
            ->with('SomeRoot.SomeObject')
            ->will($this->returnValue(true));

        $this->getValueLoaderMock()
            ->expects($this->once())
            ->method('load')
            ->will($this->throwException(new MultipleValueLoadException()));

        $this->getValueObjectVisitorDispatcherMock()
            ->expects($this->never())
            ->method('visit');

        $visitor->visit($this->getVisitorMock(), $generator, $resourceLink);

        $generator->endObjectElement('SomeObject');
        $generator->endObjectElement('SomeRoot');

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath($dom, '//SomeObject[@href="' . $resourceLink->link . '"]');
        $this->assertXPath($dom, '//SomeObject[@media-type="application/vnd.ez.api.SomeObject+xml"]');
        $this->assertXPath($dom, '//SomeObject[@embed-error="Value was already loaded"]');
    }

    public function testVisitCircularLoadException()
    {
        self::markTestIncomplete('@todo Implement feature');
    }

    public function buildValueObject()
    {
        return [
            [new ResourceLinkValue('/api/ezp/v2/resource')],
        ];
    }

    /**
     * Must return an instance of the tested visitor object.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor
     */
    protected function internalGetVisitor()
    {
        return new ResourceLink(
            $this->getValueLoaderMock(),
            $this->getPathExpansionCheckerMock(),
            $this->getValueObjectVisitorDispatcherMock()
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UriValueLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValueLoaderMock()
    {
        if ($this->valueLoaderMock === null) {
            $this->valueLoaderMock = $this
                ->getMockBuilder('eZ\Publish\Core\REST\Server\Output\PathExpansion\ValueLoaders\UriValueLoader')
                ->getMock();
        }

        return $this->valueLoaderMock;
    }

    /**
     * @return \eZ\Publish\Core\REST\Server\Output\PathExpansion\PathExpansionChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPathExpansionCheckerMock()
    {
        if ($this->pathExpansionCheckerMock === null) {
            $this->pathExpansionCheckerMock = $this
                ->getMockBuilder('eZ\Publish\Core\REST\Server\Output\PathExpansion\PathExpansionChecker')
                ->getMock();
        }

        return $this->pathExpansionCheckerMock;
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitorDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValueObjectVisitorDispatcherMock()
    {
        if ($this->valueObjectVisitorDispatcherMock === null) {
            $this->valueObjectVisitorDispatcherMock = $this
                ->getMockBuilder('eZ\Publish\Core\REST\Common\Output\ValueObjectVisitorDispatcher')
                ->getMock();
        }

        return $this->valueObjectVisitorDispatcherMock;
    }
}
