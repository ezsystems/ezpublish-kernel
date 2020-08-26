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
    /**
     * @see \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Compound::__sleep.
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $serializerSubMatchers = [];
        foreach ($object->getSubMatchers() as $matcher) {
            $serializerSubMatchers[] = parent::normalize($matcher, $format, $context);
        }

        return [
            'subMatchers' => $serializerSubMatchers,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Matcher\Compound;
    }
}
