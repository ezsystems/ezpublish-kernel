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

class ExceptionTest extends ValueObjectVisitorBaseTest
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
        $visitor   = $this->getExceptionVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $exception = new \Exception( 'Foo' );

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
                'tag'      => 'Exception',
                'children' => array(
                    'less_than'    => 3,
                    'greater_than' => 1,
                )
            ),
            $result,
            'Invalid <Exception> element.',
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
                'tag'      => 'Exception',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Exception+xml',
                    'code'       => '0',
                    'file'       => __FILE__,
                    'line'       => '37',
                )
            ),
            $result,
            'Invalid <Exception> attributes.',
            false
        );
    }

    /**
     * testResultContainsExceptionAttributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsExceptionTrace( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'trace',
                'content'  => 'regexp:(^#0 \\[internal function\\])',
            ),
            $result,
            'Invalid <Exception> attributes.',
            false
        );
    }

    /**
     * @return \eZ\Publish\API\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function getExceptionVisitor()
    {
        return new ValueObjectVisitor\Exception();
    }
}
