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
final class ContentTranslationEvaluator implements VersionTargetEvaluator
{
    public function accept(Target\Version $targetVersion): bool
    {
        return !empty($targetVersion->allLanguageCodesList);
    }

    /**
     * Allow access if any of the given language codes for translations matches any of the limitation values.
     */
    public function evaluate(Target\Version $targetVersion, Limitation $limitationValue): ?bool
    {
        $matchingTranslations = array_intersect(
            $targetVersion->allLanguageCodesList,
            $limitationValue->limitationValues
        );

        return empty($matchingTranslations)
            ? LanguageLimitationType::ACCESS_DENIED
            : LanguageLimitationType::ACCESS_GRANTED;
    }
}
