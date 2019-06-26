<?php

/**
 * File containing ValueObjectVisitorBaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Common\Tests\AssertXmlTagTrait;
use eZ\Publish\Core\REST\Server\Tests;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

abstract class ValueObjectVisitorBaseTest extends Tests\BaseTest
{
    use AssertXmlTagTrait;

    /**
     * Visitor mock.
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    protected $visitorMock;

    /**
     * Output generator.
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Generator\Xml
     */
    protected $generator;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser */
    protected $requestParser;

    /** @var \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $routerMock;

    /** @var \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $templatedRouterMock;

    /** @var int */
    private $routerCallIndex = 0;

    /** @var int */
    private $templatedRouterCallIndex = 0;

    /**
     * Gets the visitor mock.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getVisitorMock()
    {
        if (!isset($this->visitorMock)) {
            $this->visitorMock = $this->createMock(Visitor::class);

            $this->visitorMock
                ->expects($this->any())
                ->method('getResponse')
                ->will($this->returnValue($this->getResponseMock()));
        }

        return $this->visitorMock;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getResponseMock()
    {
        if (!isset($this->responseMock)) {
            $this->responseMock = $this->getMockBuilder(Response::class)
                ->getMock();
        }

        return $this->responseMock;
    }

    /**
     * Gets the output generator.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Xml
     */
    protected function getGenerator()
    {
        if (!isset($this->generator)) {
            $this->generator = new Generator\Xml(
                new Generator\Xml\FieldTypeHashGenerator()
            );
        }

        return $this->generator;
    }

    /**
     * Asserts that the given $xpathExpression returns a non empty node set
     * with $domNode as its context.
     *
     * This method asserts that $xpathExpression results in a non-empty node
     * set in context of $domNode, by wrapping the "boolean()" function around
     * it and evaluating it on the document owning $domNode.
     *
     * @param \DOMNode $domNode
     * @param string $xpathExpression
     */
    protected function assertXPath(\DOMNode $domNode, $xpathExpression)
    {
        $ownerDocument = ($domNode instanceof \DOMDOcument
            ? $domNode
            : $domNode->ownerDocument);

        $xpath = new \DOMXPath($ownerDocument);

        $this->assertTrue(
            $xpath->evaluate("boolean({$xpathExpression})", $domNode),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }

    protected function getVisitor()
    {
        $visitor = $this->internalGetVisitor();
        $visitor->setRequestParser($this->getRequestParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        return $visitor;
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\RequestParser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestParser()
    {
        if (!isset($this->requestParser)) {
            $this->requestParser = $this->createMock(RequestParser::class);
        }

        return $this->requestParser;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRouterMock()
    {
        if (!isset($this->routerMock)) {
            $this->routerMock = $this->createMock(RouterInterface::class);
        }

        return $this->routerMock;
    }

    /**
     * Resets the router mock and its expected calls index & list.
     */
    protected function resetRouterMock()
    {
        $this->routerMock = null;
        $this->routerMockCallIndex = 0;
    }

    /**
     * Adds an expectation to the routerMock. Expectations must be added sequentially.
     *
     * @param string $routeName
     * @param array $arguments
     * @param string $returnValue
     */
    protected function addRouteExpectation($routeName, $arguments, $returnValue)
    {
        $this->getRouterMock()
            ->expects($this->at($this->routerCallIndex++))
            ->method('generate')
            ->with(
                $this->equalTo($routeName),
                $this->equalTo($arguments)
            )
            ->will($this->returnValue($returnValue));
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTemplatedRouterMock()
    {
        if (!isset($this->templatedRouterMock)) {
            $this->templatedRouterMock = $this->createMock(RouterInterface::class);
        }

        return $this->templatedRouterMock;
    }

    /**
     * Adds an expectation to the templatedRouterMock. Expectations must be added sequentially.
     *
     * @param string $routeName
     * @param array $arguments
     * @param string $returnValue
     */
    protected function addTemplatedRouteExpectation($routeName, $arguments, $returnValue)
    {
        $this->getTemplatedRouterMock()
            ->expects($this->at($this->templatedRouterCallIndex++))
            ->method('generate')
            ->with(
                $this->equalTo($routeName),
                $this->equalTo($arguments)
            )
            ->will($this->returnValue($returnValue));
    }

    /**
     * Must return an instance of the tested visitor object.
     *
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor
     */
    abstract protected function internalGetVisitor();
}
