<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Common;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter as LegacySlugConverter;

/**
 * Overridden Slug Converter for test purposes (to make Service configuration mutable).
 *
 * @see \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
 */
class SlugConverter extends LegacySlugConverter
{
    /**
     * Set service-wide configuration value.
     *
     * @param string $key
     * @param string $value
     */
    public function setConfigurationValue(string $key, string $value): void
    {
        $this->configuration[$key] = $value;
    }
}
