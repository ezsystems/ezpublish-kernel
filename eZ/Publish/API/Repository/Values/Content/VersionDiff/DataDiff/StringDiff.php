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
    /** @var string */
    private $token;

    /** @var string */
    private $status;

    /**
     * @param string $token
     * @param string $status
     */
    public function __construct(string $token, string $status)
    {
        $this->token = $token;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
