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

class ExceptionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Exception visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $previousException = new \Exception('Sub-test');
        $exception = new \Exception('Test', 0, $previousException);

        $this
            ->getVisitorMock()
            ->expects($this->once())
            ->method('visitValueObject')
            ->with($previousException);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $exception
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ErrorMessage element and error code.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorCode($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorCode',
                    'content' => (string)$this->getExpectedStatusCode(),
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * Test if result contains ErrorMessage element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorMessage($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorMessage',
                    'content' => $this->getExpectedMessage(),
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * Test if result contains ErrorMessage element and description.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDescription($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorDescription',
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * Test if ErrorMessage element contains required attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsExceptionAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ErrorMessage+xml',
                ],
            ],
            $result,
            'Invalid <ErrorMessage> attributes.'
        );
    }

    /**
     * Test if result contains ErrorMessage element.
     *
     * @depends testVisit
     */
    public function testResultContainsPreviousError($result)
    {
        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath(
            $dom,
            '/ErrorMessage/Previous[@media-type="application/vnd.ez.api.ErrorMessage+xml"]'
        );
    }

    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 500;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Internal Server Error';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new \Exception('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Exception();
    }
}
