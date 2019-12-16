<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Limitation\LanguageLimitation;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Limitation\Target;

/**
 * @internal for internal use by LanguageLimitation
 */
interface VersionTargetEvaluator
{
    public function accept(Target\Version $targetVersion): bool;

    public function evaluate(Target\Version $targetVersion, Limitation $limitationValue): ?bool;
}
