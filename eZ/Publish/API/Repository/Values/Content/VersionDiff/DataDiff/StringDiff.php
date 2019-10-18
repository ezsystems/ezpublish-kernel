<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff;

use eZ\Publish\API\Repository\Values\ValueObject;

class StringDiff extends ValueObject
{
    /** @var string|null */
    private $token;

    /** @var string */
    private $status;

    public function __construct(?string $token, string $status)
    {
        $this->token = $token;
        $this->status = $status;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
