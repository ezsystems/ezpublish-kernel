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
}
