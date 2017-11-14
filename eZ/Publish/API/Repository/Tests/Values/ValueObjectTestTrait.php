<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

trait ValueObjectTestTrait
{
    /**
     * Asserts that properties given in $expectedValues are correctly set in
     * $mockedValueObject.
     *
     * @param mixed[] $expectedValues
     * @param \eZ\Publish\API\Repository\Values\ValueObject $actualValueObject
     */
    public function assertPropertiesCorrect(array $expectedValues, ValueObject $actualValueObject)
    {
        foreach ($expectedValues as $propertyName => $propertyValue) {
            self::assertSame(
                $propertyValue,
                $actualValueObject->$propertyName,
                sprintf('Property %s value is incorrect', $propertyName)
            );
        }
    }
}
