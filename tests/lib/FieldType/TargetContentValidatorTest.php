<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Core\FieldType;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\ValidationError;
use Ibexa\Core\FieldType\Validator\TargetContentValidator;
use PHPUnit\Framework\TestCase;

class TargetContentValidatorTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject */
    private $contentService;

    /** @var \Ibexa\Core\FieldType\Validator\TargetContentValidator */
    private $targetContentValidator;

    public function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->targetContentValidator = new TargetContentValidator($this->contentService);
    }

    public function testValidateWithValidContentId(): void
    {
        $id = 2;

        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($id);

        $validationError = $this->targetContentValidator->validate($id);

        self::assertNull($validationError);
    }

    /**
     * @param mixed $id
     *
     * @dataProvider providerForInvalidContentIdentifiers
     */
    public function testValidateWithInvalidContentId($id): void
    {
        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($id)
            ->willThrowException($this->createMock(NotFoundException::class));

        $validationError = $this->targetContentValidator->validate($id);

        self::assertInstanceOf(ValidationError::class, $validationError);
    }

    public function providerForInvalidContentIdentifiers(): array
    {
        return [
            ['/foo/bar'],
            ['test'],
            ['5'],
            [[]],
        ];
    }
}
