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
final class VersionTranslationUpdateEvaluator implements VersionTargetEvaluator
{
    public function accept(Target\Version $targetVersion): bool
    {
        return
            !empty($targetVersion->forUpdateLanguageCodesList)
            || null !== $targetVersion->forUpdateInitialLanguageCode;
    }

    public function evaluate(Target\Version $targetVersion, Limitation $limitationValue): ?bool
    {
        $accessVote = LanguageLimitationType::ACCESS_ABSTAIN;

        if (!empty($targetVersion->forUpdateLanguageCodesList)) {
            $diff = array_diff(
                $targetVersion->forUpdateLanguageCodesList,
                $limitationValue->limitationValues
            );
            $accessVote = empty($diff)
                ? LanguageLimitationType::ACCESS_GRANTED
                : LanguageLimitationType::ACCESS_DENIED;
        }

        if (
            $accessVote !== LanguageLimitationType::ACCESS_DENIED
            && null !== $targetVersion->forUpdateInitialLanguageCode
        ) {
            $accessVote = in_array(
                $targetVersion->forUpdateInitialLanguageCode,
                $limitationValue->limitationValues
            )
                ? LanguageLimitationType::ACCESS_GRANTED
                : LanguageLimitationType::ACCESS_DENIED;
        }

        return $accessVote;
    }
}
