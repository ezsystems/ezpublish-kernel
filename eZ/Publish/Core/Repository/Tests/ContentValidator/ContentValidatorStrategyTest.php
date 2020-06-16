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
            new class() implements ContentValidator {
                public function supports(ValueObject $object): bool
                {
                    return $object instanceof ObjectState;
                }

                public function validate(
                    ValueObject $object,
                    array $context = [],
                    ?array $fieldIdentifiers = null
                ): array {
                    return [
                        'test',
                    ];
                }
            },
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
            new class() implements ContentValidator {
                public function supports(ValueObject $object): bool
                {
                    return $object instanceof ObjectState;
                }

                public function validate(
                    ValueObject $object,
                    array $context = [],
                    ?array $fieldIdentifiers = null
                ): array {
                    return [
                        'test',
                    ];
                }
            },
        ]);

        $supports = $contentValidatorStrategy->supports(new ObjectState());

        $this->assertTrue($supports);
    }
}
