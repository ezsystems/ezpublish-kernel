<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff;

use eZ\Publish\API\Repository\Values\ValueObject;

class VersionDiff extends ValueObject
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff[] */
    private $diffPerFieldId;

    public function __construct(array $diffPerFieldId = [])
    {
        $this->diffPerFieldId = $diffPerFieldId;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldDiff
     */
    public function getDiffPerFieldId(string $fieldId): FieldDiff
    {
        return $this->diffPerFieldId[$fieldId];
    }
}
