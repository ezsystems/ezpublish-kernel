<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use DateTime;
use DateTimeImmutable;
use eZ\Publish\API\Repository\Values\ValueObject;

final class PasswordInfo extends ValueObject
{
    /** @var \DateTimeImmutable|null */
    private $expirationDate;

    /** @var \DateTimeImmutable|null */
    private $expirationWarningDate;

    public function __construct(?DateTimeImmutable $expirationDate = null, ?DateTimeImmutable $expirationWarningDate = null)
    {
        $this->expirationDate = $expirationDate;
        $this->expirationWarningDate = $expirationWarningDate;
    }

    public function isPasswordExpired(): bool
    {
        if ($this->expirationDate === null) {
            return false;
        }

        return $this->expirationDate < new DateTime();
    }

    public function hasExpirationDate(): bool
    {
        return $this->expirationDate !== null;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function hasExpirationWarningDate(): bool
    {
        return $this->expirationWarningDate !== null;
    }

    public function getExpirationWarningDate(): ?DateTimeImmutable
    {
        return $this->expirationWarningDate;
    }
}
