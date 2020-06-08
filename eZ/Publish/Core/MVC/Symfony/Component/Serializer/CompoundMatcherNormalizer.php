<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class CompoundMatcherNormalizer extends PropertyNormalizer
{
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);
        $data['config'] = [];
        $data['matchersMap'] = [];

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Matcher\Compound;
    }
}
