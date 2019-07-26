<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URLWildcard;

use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

interface BeforeTranslateEvent
{
    public function getUrl();

    public function getResult(): URLWildcardTranslationResult;

    public function setResult(?URLWildcardTranslationResult $result): void;

    public function hasResult(): bool;
}
