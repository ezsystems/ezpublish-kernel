<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation\Tests;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Limitation\LanguageLimitationType;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPIHandler;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use RuntimeException;

class LanguageLimitationTypeTest extends Base
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contentLanguageHandlerMock;

    /**
     * @var \eZ\Publish\Core\Limitation\LanguageLimitationType
     */
    private $languageLimitationType;

    /**
     * Setup Language Handler mock.
     */
    public function setUp()
    {
        parent::setUp();
        $this->contentLanguageHandlerMock = $this->createMock(SPIHandler::class);
        $this->languageLimitationType = new LanguageLimitationType($this->getPersistenceMock());
    }

    /**
     * Tear down Language Handler mock.
     */
    public function tearDown()
    {
        unset($this->contentLanguageHandlerMock, $this->languageLimitationType);
        parent::tearDown();
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValue(): array
    {
        return [
            [new LanguageLimitation()],
            [new LanguageLimitation([])],
            [new LanguageLimitation(['limitationValues' => ['2', 's3fdaf32r']])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValue
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation $limitation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAcceptValue(LanguageLimitation $limitation): void
    {
        $this->languageLimitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestAcceptValueException(): array
    {
        return [
            [new ObjectStateLimitation()],
            [new LanguageLimitation(['limitationValues' => [true]])],
            [new LanguageLimitation(['limitationValues' => [0]])],
            [new LanguageLimitation(['limitationValues' => [PHP_INT_MAX]])],
        ];
    }

    /**
     * @dataProvider providerForTestAcceptValueException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function testAcceptValueException(Limitation $limitation): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->languageLimitationType->acceptValue($limitation);
    }

    /**
     * @return array
     */
    public function providerForTestValidatePass(): array
    {
        return [
            [new LanguageLimitation()],
            [new LanguageLimitation([])],
            [new LanguageLimitation(['limitationValues' => ['pol-PL']])],
            [new LanguageLimitation(['limitationValues' => ['pol-PL', 'ger-DE']])],
        ];
    }

    /**
     * @dataProvider providerForTestValidatePass
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation $limitation
     */
    public function testValidatePass(LanguageLimitation $limitation): void
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->method('contentLanguageHandler')
                ->will($this->returnValue($this->contentLanguageHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->contentLanguageHandlerMock
                    ->expects($this->at($key))
                    ->method('loadByLanguageCode')
                    ->with($value);
            }
        }

        $limitationType = $this->languageLimitationType;

        $validationErrors = $limitationType->validate($limitation);

        self::assertEmpty($validationErrors);
    }

    /**
     * @return array
     */
    public function providerForTestValidateError(): array
    {
        return [
            [new LanguageLimitation(), 0],
            [new LanguageLimitation(['limitationValues' => [0]]), 1],
            [new LanguageLimitation(['limitationValues' => [0, PHP_INT_MAX]]), 2],
        ];
    }

    /**
     * @dataProvider providerForTestValidateError
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation $limitation
     * @param int $errorCount
     */
    public function testValidateError(LanguageLimitation $limitation, $errorCount): void
    {
        if (!empty($limitation->limitationValues)) {
            $this->getPersistenceMock()
                ->method('contentLanguageHandler')
                ->will($this->returnValue($this->contentLanguageHandlerMock));

            foreach ($limitation->limitationValues as $key => $value) {
                $this->contentLanguageHandlerMock
                    ->expects($this->at($key))
                    ->method('loadByLanguageCode')
                    ->with($value)
                    ->will($this->throwException(new NotFoundException('Language', $value)));
            }
        } else {
            $this->getPersistenceMock()
                ->expects($this->never())
                ->method($this->anything());
        }

        $limitationType = $this->languageLimitationType;

        $validationErrors = $limitationType->validate($limitation);
        self::assertCount($errorCount, $validationErrors);
    }

    public function testBuildValue(): void
    {
        $expected = ['test', 'test' => 9];
        $value = $this->languageLimitationType->buildValue($expected);

        self::assertInstanceOf(LanguageLimitation::class, $value);
        self::assertInternalType('array', $value->limitationValues);
        self::assertEquals($expected, $value->limitationValues);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluate(): array
    {
        return [
            // ContentInfo, no access
            [
                'limitation' => new LanguageLimitation(),
                'object' => new ContentInfo(),
                'expected' => false,
            ],
            // ContentInfo, with access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentInfo(['mainLanguageCode' => 'pol-PL']),
                'expected' => true,
            ],
            // ContentInfo, no access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentInfo(['mainLanguageCode' => 'eng-GB']),
                'expected' => false,
            ],
            // ContentCreateStruct, with access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentCreateStruct(['mainLanguageCode' => 'pol-PL']),
                'expected' => true,
            ],
            // ContentCreateStruct, no access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentCreateStruct(['mainLanguageCode' => 'eng-GB']),
                'expected' => false,
            ],
            // ContentUpdateStruct, with access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentUpdateStruct(['initialLanguageCode' => 'pol-PL']),
                'expected' => true,
            ],
            // ContentUpdateStruct, no access
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['pol-PL']]),
                'object' => new ContentUpdateStruct(['initialLanguageCode' => 'eng-GB']),
                'expected' => false,
            ],
            // VersionInfo, with access, VersionInfo initialLanguageCode (pol-PL) is in array of limitationValues
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['eng-GB', 'pol-PL']]),
                'object' => $this->getVersionInfo(),
                'expected' => true,
            ],
            // VersionInfo, with access, one of limitationValues is in VersionInfo->languageCodes
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['eng-GB', 'pol-PL']]),
                'object' => $this->getVersionInfo('fra-FR', ['pol-PL']),
                'expected' => true,
            ],
            // VersionInfo, no access, VersionInfo initialLanguageCode (pol-PL) is NOT in array of limitationValues
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['eng-GB', 'pol-PL']]),
                'object' => $this->getVersionInfo('fra-FR'),
                'expected' => false,
            ],
            // VersionInfo, no access, NONE of limitationValues is in VersionInfo->languageCodes
            [
                'limitation' => new LanguageLimitation(['limitationValues' => ['eng-GB', 'pol-PL']]),
                'object' => $this->getVersionInfo('fra-FR', ['nor-NO']),
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluate
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation $limitation
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param $expected
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluate(
        LanguageLimitation $limitation,
        ValueObject $object,
        $expected
    ): void {
        $limitationType = $this->languageLimitationType;

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $value = $limitationType->evaluate(
            $limitation,
            $userMock,
            $object
        );

        self::assertInternalType('boolean', $value);
        self::assertEquals($expected, $value);
    }

    /**
     * @return array
     */
    public function providerForTestEvaluateInvalidArgument(): array
    {
        return [
            // invalid limitation
            [
                'limitation' => new ObjectStateLimitation(),
                'object' => new ContentInfo(),
                'targets' => null,
                'persistence' => [],
            ],
            // invalid object
            [
                'limitation' => new LanguageLimitation(),
                'object' => new ObjectStateLimitation(),
                'targets' => [new Location()],
                'persistence' => [],
            ],
        ];
    }

    /**
     * @dataProvider providerForTestEvaluateInvalidArgument
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param $targets
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testEvaluateInvalidArgument(
        Limitation $limitation,
        ValueObject $object,
        $targets
    ): void {
        $limitationType = $this->languageLimitationType;

        $userMock = $this->getUserMock();
        $userMock
            ->expects($this->never())
            ->method($this->anything());

        $persistenceMock = $this->getPersistenceMock();
        $persistenceMock
            ->expects($this->never())
            ->method($this->anything());

        $this->expectException(InvalidArgumentException::class);

        $limitationType->evaluate(
            $limitation,
            $userMock,
            $object,
            $targets
        );
    }

    public function testGetCriterionInvalidValue(): void
    {
        $this->expectException(RuntimeException::class);

        $this->languageLimitationType->getCriterion(
            new LanguageLimitation([]),
            $this->getUserMock()
        );
    }

    public function testGetCriterionSingleValue(): void
    {
        $criterion = $this->languageLimitationType->getCriterion(
            new LanguageLimitation(['limitationValues' => ['pol-PL']]),
            $this->getUserMock()
        );

        self::assertInstanceOf(LanguageCode::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::EQ, $criterion->operator);
        self::assertEquals(['pol-PL'], $criterion->value);
    }

    public function testGetCriterionMultipleValues(): void
    {
        $criterion = $this->languageLimitationType->getCriterion(
            new LanguageLimitation(['limitationValues' => ['pol-PL', 'ger-DE']]),
            $this->getUserMock()
        );

        self::assertInstanceOf(LanguageCode::class, $criterion);
        self::assertInternalType('array', $criterion->value);
        self::assertInternalType('string', $criterion->operator);
        self::assertEquals(Operator::IN, $criterion->operator);
        self::assertEquals(['pol-PL', 'ger-DE'], $criterion->value);
    }

    public function testValueSchema(): void
    {
        $this->expectException(NotImplementedException::class);

        $this->languageLimitationType->valueSchema();
    }

    /**
     * @param string $initialLanguageCode
     * @param array $languageCodes
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getVersionInfo(string $initialLanguageCode = 'pol-PL', array $languageCodes = []): VersionInfo
    {
        $versionInfo = $this->createMock(VersionInfo::class);
        $versionInfo
            ->method('__get')
            ->will(
                $this->returnValueMap(
                    [
                        ['initialLanguageCode', $initialLanguageCode],
                        ['languageCodes', $languageCodes],
                    ]
                )
            );

        return $versionInfo;
    }
}
