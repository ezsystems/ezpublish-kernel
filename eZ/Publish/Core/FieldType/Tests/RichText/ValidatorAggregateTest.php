<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\RichText;

use DOMDocument;
use eZ\Publish\Core\FieldType\RichText\ValidatorAggregate;
use eZ\Publish\Core\FieldType\RichText\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidatorAggregateTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\ValidatorAggregate::validateDocument
     */
    public function testValidateDocument(): void
    {
        $doc = $this->createMock(DOMDocument::class);

        $expectedErrors = [];
        $validators = [];

        for ($i = 0; $i < 3; ++$i) {
            $errorMessage = "Validation error $i";

            $validator = $this->createMock(ValidatorInterface::class);
            $validator
                ->expects($this->once())
                ->method('validateDocument')
                ->with($doc)
                ->willReturn([$errorMessage]);

            $expectedErrors[] = $errorMessage;
            $validators[] = $validator;
        }

        $aggregate = new ValidatorAggregate($validators);
        $actualErrors = $aggregate->validateDocument($doc);

        $this->assertEquals($expectedErrors, $actualErrors);
    }
}
