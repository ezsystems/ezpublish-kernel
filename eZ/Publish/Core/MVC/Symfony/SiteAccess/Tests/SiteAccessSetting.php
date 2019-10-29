<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Tests;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * This class represents settings which will be used to construct SiteAccessProvider mock.
 */
final class SiteAccessSetting
{
    /** @var string */
    public $name;

    /** @var bool */
    public $isDefined;

    /** @var string */
    public $matchingType;

    public function __construct(
        string $name,
        bool $isDefined,
        string $matchingType = SiteAccess::DEFAULT_MATCHING_TYPE
    ) {
        $this->name = $name;
        $this->isDefined = $isDefined;
        $this->matchingType = $matchingType;
    }
}
