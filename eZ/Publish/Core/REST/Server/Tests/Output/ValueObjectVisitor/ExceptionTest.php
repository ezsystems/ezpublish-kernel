<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common;

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
     * Test the Exception visitor
     *
     * @return string
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
     * Test if result contains ErrorMessage element and error code
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorCode( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ErrorMessage',
                'descendant' => array(
                    'tag'     => 'errorCode',
                    'content' => (string)$this->getExpectedStatusCode(),
                )
            ),
            $result,
            'Invalid <ErrorMessage> element.',
            false
        );
    }

    /**
     * Test if result contains ErrorMessage element
     *
     * @param string $result
     *
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
     * Test if result contains ErrorMessage element and description
     *
     * @param string $result
     *
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
     * Test if ErrorMessage element contains required attributes
     *
     * @param string $result
     *
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
     * Gets the exception
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new \Exception( "Test" );
    }

    /**
     * Gets the exception visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function getExceptionVisitor()
    {
        return new ValueObjectVisitor\Exception(
            new Common\UrlHandler\eZPublish()
        );
    }
}
