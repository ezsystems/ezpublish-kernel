<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;
use Iterator;
use IteratorAggregate;
use OutOfBoundsException;

class VersionDiff extends ValueObject implements IteratorAggregate
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldValueDiff[] */
    private $fieldValueDiffs;

    public function __construct(array $fieldDiffs = [])
    {
        $this->fieldValueDiffs = $fieldDiffs;
    }

    public function getFieldValueDiffByIdentifier(string $fieldIdentifier): FieldValueDiff
    {
        if (!isset($this->fieldValueDiffs[$fieldIdentifier])) {
            throw new OutOfBoundsException(
                sprintf(
                    'There is no diff for field with "%s" identifier.',
                    $fieldIdentifier,
                )
            );
        }

        return $this->fieldValueDiffs[$fieldIdentifier];
    }

    public function isChanged(): bool
    {
        foreach ($this->fieldValueDiffs as $fieldDiff) {
            if ($fieldDiff->isChanged()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldValueDiff[]
     */
    public function getFieldValueDiffs(): array
    {
        return $this->fieldValueDiffs;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->fieldValueDiffs);
    }
}
