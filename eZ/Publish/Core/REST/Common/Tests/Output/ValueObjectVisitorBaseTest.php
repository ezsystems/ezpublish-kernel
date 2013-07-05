<?php
/**
 * File containing ValueObjectVisitorBaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Server\Tests;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\RequestParser as RequestParser;

abstract class ValueObjectVisitorBaseTest extends Tests\BaseTest
{
    /**
     * Visitor mock
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    protected $visitorMock;

    /**
     * Output generator
     *
     * @var \eZ\Publish\Core\REST\Common\Output\Generator\Xml
     */
    protected $generator;

    /**
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    protected $requestParser;

    /**
     * @var \Symfony\Component\Routing\RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routerMock;

    /**
     * @var int
     */
    private $routerCallIndex = 0;

    /**
     * Gets the visitor mock
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    protected function getVisitorMock()
    {
        if ( !isset( $this->visitorMock ) )
        {
            $this->visitorMock = $this->getMock(
                '\\eZ\\Publish\\Core\\REST\\Common\\Output\\Visitor',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->visitorMock;
    }

    /**
     * Gets the output generator
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator\Xml
     */
    protected function getGenerator()
    {
        if ( !isset( $this->generator ) )
        {
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
    protected function assertXPath( \DOMNode $domNode, $xpathExpression )
    {
        $ownerDocument = ( $domNode instanceof \DOMDOcument
            ? $domNode
            : $domNode->ownerDocument );

        $xpath = new \DOMXPath( $ownerDocument );

        $this->assertTrue(
            $xpath->evaluate( "boolean({$xpathExpression})", $domNode ),
            "XPath expression '{$xpathExpression}' resulted in an empty node set."
        );
    }

    protected function getVisitor()
    {
        $visitor = $this->internalGetVisitor();
        $visitor->setRequestParser( $this->getRequestParser() );
        $visitor->setRouter( $this->getRouterMock() );
        return $visitor;
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\RequestParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestParser()
    {
        if ( !isset( $this->requestParser ) )
        {
            $this->requestParser = $this->getMock( 'eZ\\Publish\\Core\\REST\\Common\\RequestParser' );
        }
        return $this->requestParser;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRouterMock()
    {
        if ( !isset( $this->routerMock ) )
        {
            $this->routerMock = $this->getMock( 'Symfony\\Component\\Routing\\RouterInterface' );
        }

        return $this->routerMock;
    }

    /**
     * Resets the router mock and its expected calls index & list
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
    protected function addRouteExpectation( $routeName, $arguments, $returnValue )
    {
        $this->getRouterMock()
            ->expects( $this->at( $this->routerCallIndex++ ) )
            ->method( 'generate' )
            ->with(
                $this->equalTo( $routeName ),
                $this->equalTo( $arguments )
            )
            ->will( $this->returnValue( $returnValue ) );
    }

    /**
     * Must return an instance of the tested visitor object
     *
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor
     */
    abstract protected function internalGetVisitor();
}
