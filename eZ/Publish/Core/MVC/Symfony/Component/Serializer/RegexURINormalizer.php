<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Regex\URI;

final class RegexURINormalizer extends AbstractPropertyWhitelistNormalizer
{
    protected function getAllowedProperties()
    {
        return ['siteAccessesConfiguration'];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof URI;
    }
}
