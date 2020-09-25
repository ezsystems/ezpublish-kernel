<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

final class MapNormalizer extends PropertyNormalizer
{
    /**
     * @see \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map::__sleep
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'key' => $object->getMapKey(),
            'map' => [],
            'reverseMap' => [],
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Map;
    }
}
