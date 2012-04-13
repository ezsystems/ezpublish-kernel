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

        $exception = $this->getException();

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
    public function testResultContainsErrorCode( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ErrorMessage',
                'descendant' => array(
                    'tag'     => 'errorCode',
                    'content' => (string) $this->getExpectedStatusCode(),
                )
            ),
            $result,
            'Invalid <ErrorMessage> element.',
            false
        );
    }

    /**
     * testResultContainsExceptionElement
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsErrorMessage( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ErrorMessage',
                'descendant' => array(
                    'tag'     => 'errorMessage',
                    'content' => $this->getExpectedMessage(),
                )
            ),
            $result,
            'Invalid <ErrorMessage> element.',
            false
        );
    }

    /**
     * testResultContainsExceptionElement
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsErrorDescription( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ErrorMessage',
                'descendant' => array(
                    'tag' => 'errorDescription',
                )
            ),
            $result,
            'Invalid <ErrorMessage> element.',
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
                'tag'      => 'ErrorMessage',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ErrorMessage+xml',
                )
            ),
            $result,
            'Invalid <ErrorMessage> attributes.',
            false
        );
    }

    /**
     * Get expected status code
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 500;
    }

    /**
     * Get expected message
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return "Internal Server Error";
    }

    /**
     * @return \Exception
     */
    protected function getException()
    {
        return new \Exception( "Test" );
    }

    /**
     * @return \eZ\Publish\API\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function getExceptionVisitor()
    {
        return new ValueObjectVisitor\Exception();
    }
}
