<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\UserPreference;

use eZ\Publish\Core\Event\AfterEvent;

final class SetUserPreferenceEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.user_preference.set';

    /**
     * @var array
     */
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
