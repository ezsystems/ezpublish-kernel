<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Tests\Stubs\Exceptions;

class BadStateExceptionExceptionTest extends ValueObjectVisitorBaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     *
     * @todo: This and its creation could be moved to common base test class
     *        for input parsers.
     */
    protected $visitor;

    /**
     * testVisit
     *
     * @return void
     */
    public function testVisit()
    {
        $visitor   = $this->getBadStateExceptionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $exception = new Exceptions\BadStateExceptionStub( 'Foo not found' );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $exception
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * testResultContainsExceptionElement
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsExceptionElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'BadStateException',
                'children' => array(
                    'less_than'    => 2,
                    'greater_than' => 0,
                )
            ),
            $result,
            'Invalid <BadStateException> element.',
            false
        );
    }

    /**
     * testResultContainsExceptionAttributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsExceptionAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'BadStateException',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.BadStateException+xml',
                    'code'       => '0',
                    'file'       => __FILE__,
                    'line'       => '38',
                )
            ),
            $result,
            'Invalid <BadStateException> attributes.',
            false
        );
    }

    /**
     * @return \eZ\Publish\API\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function getBadStateExceptionVisitor()
    {
        return new ValueObjectVisitor\BadStateException();
    }
}
