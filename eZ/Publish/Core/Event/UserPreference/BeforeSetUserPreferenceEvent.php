<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\UserPreference;

use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeSetUserPreferenceEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\UserPreference\UserPreferenceSetStruct[] */
    private $userPreferenceSetStructs;

    public function __construct(array $userPreferenceSetStructs)
    {
        $this->userPreferenceSetStructs = $userPreferenceSetStructs;
    }

    public function getUserPreferenceSetStructs(): array
    {
        return $this->userPreferenceSetStructs;
    }
}
