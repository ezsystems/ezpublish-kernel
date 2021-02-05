<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Limitation\LanguageLimitation;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Limitation\LanguageLimitationType;
use eZ\Publish\SPI\Limitation\Target;

/**
 * @internal for internal use by LanguageLimitation
 */
final class ContentDeleteEvaluator implements VersionTargetEvaluator
{
    public function accept(Target\Version $targetVersion): bool
    {
        return !empty($targetVersion->getTranslationsToDelete());
    }

    /**
     * Allow access if all of the given language codes for content matches limitation values.
     */
    public function evaluate(Target\Version $targetVersion, Limitation $limitationValue): ?bool
    {
        $diff = array_diff(
            $targetVersion->getTranslationsToDelete(),
            $limitationValue->limitationValues
        );
        $accessVote = empty($diff)
            ? LanguageLimitationType::ACCESS_GRANTED
            : LanguageLimitationType::ACCESS_DENIED;

        return $accessVote;
    }
}
