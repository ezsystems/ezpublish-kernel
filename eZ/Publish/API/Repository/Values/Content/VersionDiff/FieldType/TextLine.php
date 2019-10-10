<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\DiffValue;

class TextLine extends DiffValue
{
    private $stringDiffs;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff[] $stringDiffs
     */
    public function __construct(array $stringDiffs)
    {
        $this->stringDiffs = $stringDiffs;
    }

    /**
     * @return array|\eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff[]
     */
    public function getStringDiffs()
    {
        return $this->stringDiffs;
    }

}
