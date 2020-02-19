<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use DOMDocument;
use DOMXPath;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Exception as ExceptionValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use Symfony\Component\Translation\TranslatorInterface;

class ExceptionTest extends ValueObjectVisitorBaseTest
{
    const NON_VERBOSE_ERROR_DESCRIPTION = 'An error has occurred. Please try again later or contact your Administrator.';

    /** @var \Symfony\Component\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translatorMock;

    /**
     * Test the Exception visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $result = $this->generateDocument($generator, $visitor);

        $this->assertNotNull($result);

        return $result;
    }

    public function testVisitNonVerbose()
    {
        $this->getTranslatorMock()->method('trans')
             ->with('non_verbose_error', [], 'repository_exceptions')
             ->willReturn(self::NON_VERBOSE_ERROR_DESCRIPTION);

        $visitor = $this->internalGetNonDebugVisitor();
        $visitor->setRequestParser($this->getRequestParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        $generator = $this->getGenerator();

        $result = $this->generateDocument($generator, $visitor);

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
     * @depends testVisitNonVerbose
     */
    public function testNonVerboseErrorDescription($result)
    {
        $document = new DOMDocument();
        $document->loadXML($result);
        $xpath = new DOMXPath($document);

        $nodeList = $xpath->query('//ErrorMessage/errorDescription');
        $errorDescriptionNode = $nodeList->item(0);

        $this->assertEquals(self::NON_VERBOSE_ERROR_DESCRIPTION, $errorDescriptionNode->textContent);
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
        return new ExceptionValueObjectVisitor(true, $this->getTranslatorMock());
    }

    /**
     * Gets the exception visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Exception
     */
    protected function internalGetNonDebugVisitor()
    {
        return new ExceptionValueObjectVisitor(false, $this->getTranslatorMock());
    }

    protected function getTranslatorMock()
    {
        if (!isset($this->translatorMock)) {
            $this->translatorMock = $this->getMockBuilder(TranslatorInterface::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        }

        return $this->translatorMock;
    }

    private function generateDocument(Generator\Xml $generator, ValueObjectVisitor $visitor)
    {
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

        return $generator->endDocument(null);
    }
}
