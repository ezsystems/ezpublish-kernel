<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff;

use eZ\Publish\API\Repository\Values\ValueObject;
use OutOfBoundsException;

class VersionDiff extends ValueObject
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff[] */
    private $fieldDiffs;

    public function __construct(array $fieldDiffs = [])
    {
        $this->fieldDiffs = $fieldDiffs;
    }

    public function getFieldDiffByIdentifier(string $fieldIdentifier): FieldDiff
    {
        if (!isset($this->fieldDiffs[$fieldIdentifier])) {
            throw new OutOfBoundsException(
                sprintf(
                    'There is no diff for field with "%s" identifier.',
                    $fieldIdentifier,
                )
            );
        }

        return $this->fieldDiffs[$fieldIdentifier];
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff[]
     */
    public function getFieldDiffs(): array
    {
        return $this->fieldDiffs;
    }
}
