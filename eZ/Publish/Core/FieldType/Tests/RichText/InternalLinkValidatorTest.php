<?php

/**
 * File containing the InternalLinkValidatorTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\RichText;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\RichText\InternalLinkValidator;
use PHPUnit\Framework\TestCase;

class InternalLinkValidatorTest extends TestCase
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $locationHandler;

    /**
     * @before
     */
    public function setupInternalLinkValidator()
    {
        $this->contentHandler = $this->createMock(ContentHandler::class);
        $this->locationHandler = $this->createMock(LocationHandler::class);
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument 'eznull' is invalid: Given scheme 'eznull' is not supported.
     */
    public function testValidateFailOnNotSupportedSchema()
    {
        $validator = $this->getInternalLinkValidator();
        $validator->validate('eznull', 1);
    }

    public function testValidateEzContentWithExistingContentId()
    {
        $validator = $this->getInternalLinkValidator();

        $contentId = 1;
        $this->contentHandler
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId);

        $this->assertTrue($validator->validate('ezcontent', $contentId));
    }

    public function testValidateEzContentNonExistingContentId()
    {
        $validator = $this->getInternalLinkValidator();

        $contentId = 1;
        $exception = $this->createMock(NotFoundException::class);

        $this->contentHandler
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->willThrowException($exception);

        $this->assertFalse($validator->validate('ezcontent', $contentId));
    }

    public function testValidateEzLocationWithExistingLocationId()
    {
        $validator = $this->getInternalLinkValidator();

        $locationId = 1;

        $this->locationHandler
            ->expects($this->once())
            ->method('load')
            ->with($locationId);

        $this->assertTrue($validator->validate('ezlocation', $locationId));
    }

    public function testValidateEzLocationWithNonExistingLocationId()
    {
        $validator = $this->getInternalLinkValidator();

        $locationId = 1;
        $exception = $this->createMock(NotFoundException::class);

        $this->locationHandler
            ->expects($this->once())
            ->method('load')
            ->with($locationId)
            ->willThrowException($exception);

        $this->assertFalse($validator->validate('ezlocation', $locationId));
    }

    public function testValidateEzRemoteWithExistingRemoteId()
    {
        $validator = $this->getInternalLinkValidator();

        $contentRemoteId = '0ba685755118cf95abb0fe25f3f6a1c8';

        $this->contentHandler
            ->expects($this->once())
            ->method('loadContentInfoByRemoteId')
            ->with($contentRemoteId);

        $this->assertTrue($validator->validate('ezremote', $contentRemoteId));
    }

    public function testValidateEzRemoteWithNonExistingRemoteId()
    {
        $validator = $this->getInternalLinkValidator();

        $contentRemoteId = '0ba685755118cf95abb0fe25f3f6a1c8';
        $exception = $this->createMock(NotFoundException::class);

        $this->contentHandler
            ->expects($this->once())
            ->method('loadContentInfoByRemoteId')
            ->with($contentRemoteId)
            ->willThrowException($exception);

        $this->assertFalse($validator->validate('ezremote', $contentRemoteId));
    }

    public function testValidateDocumentSkipMissingTargetId()
    {
        $scheme = 'ezcontent';
        $contentId = null;

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->never())
            ->method('validate')
            ->with($scheme, $contentId);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $contentId)
        );

        $this->assertEmpty($errors);
    }

    public function testValidateDocumentEzContentExistingContentId()
    {
        $scheme = 'ezcontent';
        $contentId = 1;

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $contentId)
            ->willReturn(true);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $contentId)
        );

        $this->assertEmpty($errors);
    }

    public function testValidateDocumentEzContentNonExistingContentId()
    {
        $scheme = 'ezcontent';
        $contentId = 1;

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $contentId)
            ->willReturn(false);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $contentId)
        );

        $this->assertCount(1, $errors);
        $this->assertContainsEzContentInvalidLinkError($contentId, $errors);
    }

    public function testValidateDocumentEzContentExistingLocationId()
    {
        $scheme = 'ezlocation';
        $locationId = 1;

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $locationId)
            ->willReturn(true);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $locationId)
        );

        $this->assertEmpty($errors);
    }

    public function testValidateDocumentEzContentNonExistingLocationId()
    {
        $scheme = 'ezlocation';
        $locationId = 1;

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $locationId)
            ->willReturn(false);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $locationId)
        );

        $this->assertCount(1, $errors);
        $this->assertContainsEzLocationInvalidLinkError($locationId, $errors);
    }

    public function testValidateDocumentEzRemoteExistingId()
    {
        $scheme = 'ezremote';
        $contentRemoteId = '0ba685755118cf95abb0fe25f3f6a1c8';

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $contentRemoteId)
            ->willReturn(true);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $contentRemoteId)
        );

        $this->assertEmpty($errors);
    }

    public function testValidateDocumentEzRemoteNonExistingId()
    {
        $scheme = 'ezremote';
        $contentRemoteId = '0ba685755118cf95abb0fe25f3f6a1c8';

        $validator = $this->getInternalLinkValidator(['validate']);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with($scheme, $contentRemoteId)
            ->willReturn(false);

        $errors = $validator->validateDocument(
            $this->createInputDocument($scheme, $contentRemoteId)
        );

        $this->assertCount(1, $errors);
        $this->assertContainsEzRemoteInvalidLinkError($contentRemoteId, $errors);
    }

    private function assertContainsEzLocationInvalidLinkError($locationId, array $errors)
    {
        $format = 'Invalid link "ezlocation://%d": target location cannot be found';

        $this->assertContains(sprintf($format, $locationId), $errors);
    }

    private function assertContainsEzContentInvalidLinkError($contentId, array $errors)
    {
        $format = 'Invalid link "ezcontent://%d": target content cannot be found';

        $this->assertContains(sprintf($format, $contentId), $errors);
    }

    private function assertContainsEzRemoteInvalidLinkError($contentId, array $errors)
    {
        $format = 'Invalid link "ezremote://%s": target content cannot be found';

        $this->assertContains(sprintf($format, $contentId), $errors);
    }

    /**
     * @return \eZ\Publish\Core\FieldType\RichText\InternalLinkValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getInternalLinkValidator(array $methods = null)
    {
        return $this->getMockBuilder(InternalLinkValidator::class)
            ->setMethods($methods)
            ->setConstructorArgs([
                $this->contentHandler,
                $this->locationHandler,
            ])
            ->getMock();
    }

    private function createInputDocument($scheme, $id)
    {
        $url = $scheme . '://' . $id;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
    <para>
        <link xlink:href="' . $url . '">Content link</link>
    </para>
</section>';

        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        return $doc;
    }
}
