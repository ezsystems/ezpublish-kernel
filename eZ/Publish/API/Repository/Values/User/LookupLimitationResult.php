<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a LookupLimitation for module and function in the context of current User.
 */
final class LookupLimitationResult extends ValueObject
{
    /** @var bool */
    protected $hasAccess;

    /** @var \eZ\Publish\API\Repository\Values\User\Limitation[] */
    protected $roleLimitations;

    /** @var \eZ\Publish\API\Repository\Values\User\LookupPolicyLimitations[] */
    protected $lookupPolicyLimitations;

    /**
     * @param bool $hasAccess
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $roleLimitations
     * @param \eZ\Publish\API\Repository\Values\User\LookupPolicyLimitations[] $lookupPolicyLimitations
     */
    public function __construct(
        bool $hasAccess,
        array $roleLimitations = [],
        array $lookupPolicyLimitations = []
    ) {
        parent::__construct();

        $this->hasAccess = $hasAccess;
        $this->lookupPolicyLimitations = $lookupPolicyLimitations;
        $this->roleLimitations = $roleLimitations;
    }
}
