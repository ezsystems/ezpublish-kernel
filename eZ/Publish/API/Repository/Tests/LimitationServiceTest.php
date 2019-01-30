<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\FieldType\ValidationError;
use eZ\Publish\SPI\Limitation\Type;

/**
 * Test case for the LimitationService.
 *
 * @see \eZ\Publish\API\Repository\LimitationService
 */
class LimitationServiceTest extends BaseTest
{
    private const VALID_LIMITATION_VALUE = 2;
    private const INVALID_LIMITATION_VALUE = 9999;

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::getLimitationType()
     */
    public function testGetLimitationType(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        $limitationType = $limitationService->getLimitationType(Limitation::CONTENTTYPE);
        /* END: Use Case */

        $this->assertInstanceOf(Type::class, $limitationType);
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::getLimitationType()
     */
    public function testGetLimitationTypeThrowsLimitationNotFoundExceptionOnInvalidIdentifier(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();

        $this->expectException(LimitationNotFoundException::class);
        $limitationService->getLimitationType('foo');
        /* END: Use Case */
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::validateLimitation()
     */
    public function testValidateLimitation(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        // $userPreferenceName is the name of an existing preference
        $locationLimitation = new Limitation\LocationLimitation(['limitationValues' => [self::VALID_LIMITATION_VALUE]]);
        $errors = $limitationService->validateLimitation($locationLimitation);
        /* END: Use Case */

        $this->assertEquals($errors, []);
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::validateLimitation()
     */
    public function testValidateLimitationReturnError(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        // $userPreferenceName is the name of an existing preference
        $locationLimitation = new Limitation\LocationLimitation(['limitationValues' => [self::INVALID_LIMITATION_VALUE]]);
        $errors = $limitationService->validateLimitation($locationLimitation);
        /* END: Use Case */

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(
            ValidationError::class,
            $errors[0]
        );

        $this->assertEquals(
            new Message(
                "limitationValues[%key%] => '%value%' does not exist in the backend",
                ['value' => self::INVALID_LIMITATION_VALUE, 'key' => 0]
            ),
            $errors[0]->getTranslatableMessage()
        );
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::validateLimitation()
     */
    public function testValidateLimitationThrowsBadStateExceptionOnInvalidLimitationIdentifier(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        // $userPreferenceName is the name of an existing preference

        $limitationMock = $this->createMock(Limitation::class);
        $limitationMock
            ->method('getIdentifier')
            ->willReturn('foo');

        $this->expectException(BadStateException::class);
        $limitationService->validateLimitation($limitationMock);
        /* END: Use Case */
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::validateLimitations()
     *
     * @depends testValidateLimitation
     * @depends testValidateLimitationReturnError
     * @depends testValidateLimitationThrowsBadStateExceptionOnInvalidLimitationIdentifier
     */
    public function testValidateLimitations(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        // $userPreferenceName is the name of an existing preference
        $locationLimitation = new Limitation\LocationLimitation(['limitationValues' => [self::VALID_LIMITATION_VALUE]]);
        $sectionLimitation = new Limitation\SectionLimitation(['limitationValues' => [self::VALID_LIMITATION_VALUE]]);
        $errors = $limitationService->validateLimitations([$locationLimitation, $sectionLimitation]);
        /* END: Use Case */

        $this->assertEquals($errors, []);
    }

    /**
     * @covers \eZ\Publish\API\Repository\LimitationService::validateLimitations()
     *
     * @depends testValidateLimitation
     * @depends testValidateLimitationReturnError
     * @depends testValidateLimitationThrowsBadStateExceptionOnInvalidLimitationIdentifier
     */
    public function testValidateLimitationsReturnError(): void
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $limitationService = $repository->getLimitationService();
        // $userPreferenceName is the name of an existing preference
        $locationLimitation = new Limitation\LocationLimitation(['limitationValues' => [self::VALID_LIMITATION_VALUE]]);
        $sectionLimitation = new Limitation\SectionLimitation(['limitationValues' => [self::INVALID_LIMITATION_VALUE]]);
        $errors = $limitationService->validateLimitations([$locationLimitation, $sectionLimitation]);
        /* END: Use Case */

        $this->assertCount(1, $errors['Section']);
        $this->assertInstanceOf(
            ValidationError::class,
            $errors['Section'][0]
        );

        $this->assertEquals(
            new Message(
                "limitationValues[%key%] => '%value%' does not exist in the backend",
                ['value' => self::INVALID_LIMITATION_VALUE, 'key' => 0]
            ),
            $errors['Section'][0]->getTranslatableMessage()
        );
    }
}
