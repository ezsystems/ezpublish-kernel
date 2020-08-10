<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\ContentValidator;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\Strategy\ContentValidator\ContentValidatorStrategy;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\SPI\Repository\Validator\ContentValidator;
use PHPUnit\Framework\TestCase;

class ContentValidatorStrategyTest extends TestCase
{
    public function testUnknownValidationObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'$object\' is invalid: Validator for eZ\Publish\Core\Repository\Values\ObjectState\ObjectState type not found.');

        $contentValidatorStrategy = new ContentValidatorStrategy([]);
        $contentValidatorStrategy->validate(new ObjectState());
    }

    public function testKnownValidationObject(): void
    {
        $contentValidatorStrategy = new ContentValidatorStrategy([
            $this->buildContentValidator(ObjectState::class, ['test']),
        ]);

        $errors = $contentValidatorStrategy->validate(new ObjectState());
        $this->assertEquals(['test'], $errors);
    }

    public function testSupportsUnknownValidationObject(): void
    {
        $contentValidatorStrategy = new ContentValidatorStrategy([]);
        $supports = $contentValidatorStrategy->supports(new ObjectState());

        $this->assertFalse($supports);
    }

    public function testSuportsKnownValidationObject(): void
    {
        $contentValidatorStrategy = new ContentValidatorStrategy([
            $this->buildContentValidator(ObjectState::class, ['test']),
        ]);

        $supports = $contentValidatorStrategy->supports(new ObjectState());

        $this->assertTrue($supports);
    }

    public function testMergeValidationErrors(): void
    {
        $contentValidatorStrategy = new ContentValidatorStrategy([
            $this->buildContentValidator(ObjectState::class, [
                123 => ['eng-GB' => '123-eng-GB'],
                456 => ['pol-PL' => '456-pol-PL'],
            ]),
            $this->buildContentValidator(ObjectState::class, []),
            $this->buildContentValidator(ObjectState::class, [
                321 => ['pol-PL' => '321-pol-PL'],
            ]),
            $this->buildContentValidator(ObjectState::class, [
                2345 => ['eng-GB' => '2345-eng-GB'],
                456 => ['eng-GB' => '456-eng-GB'],
            ]),
        ]);

        $errors = $contentValidatorStrategy->validate(new ObjectState());
        $this->assertEquals([
            123 => ['eng-GB' => '123-eng-GB'],
            321 => ['pol-PL' => '321-pol-PL'],
            456 => [
                'pol-PL' => '456-pol-PL',
                'eng-GB' => '456-eng-GB',
            ],
            2345 => ['eng-GB' => '2345-eng-GB'],
        ], $errors);
    }

    private function buildContentValidator(string $classSupport, array $validationReturn): ContentValidator
    {
        return new class($classSupport, $validationReturn) implements ContentValidator {
            /** @var string */
            private $classSupport;

            /** @var array */
            private $validationReturn;

            public function __construct(
                string $classSupport,
                array $validationReturn
            ) {
                $this->classSupport = $classSupport;
                $this->validationReturn = $validationReturn;
            }

            public function supports(ValueObject $object): bool
            {
                return $object instanceof $this->classSupport;
            }

            public function validate(
                ValueObject $object,
                array $context = [],
                ?array $fieldIdentifiers = null
            ): array {
                return $this->validationReturn;
            }
        };
    }
}
