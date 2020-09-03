<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\HostText;

final class HostTextNormalizer extends AbstractPropertyWhitelistNormalizer
{
    protected function getAllowedProperties(): array
    {
        return ['siteAccessesConfiguration'];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof HostText;
    }
}
