<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

class CompoundMatcherNormalizer extends AbstractPropertyWhitelistNormalizer
{
    /**
     * @see \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound::__sleep.
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);
        $data['config'] = [];
        $data['matchersMap'] = [];

        return $data;
    }

    protected function getAllowedProperties(): array
    {
        return ['subMatchers'];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Matcher\Compound;
    }
}
