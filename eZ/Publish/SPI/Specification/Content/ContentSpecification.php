<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Specification\Content;

use eZ\Publish\API\Repository\Values\Content\Content;

interface ContentSpecification
{
    public function isSatisfiedBy(Content $content): bool;
}
