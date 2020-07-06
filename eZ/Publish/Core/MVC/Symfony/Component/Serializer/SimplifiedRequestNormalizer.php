<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

final class SimplifiedRequestNormalizer extends PropertyNormalizer
{
    /**
     * @see \Symfony\Component\Serializer\Normalizer\NormalizerInterface::normalize
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'scheme' => $object->scheme,
            'host' => $object->host,
            'port' => $object->port,
            'pathinfo' => $object->pathinfo,
            'queryParams' => $object->queryParams,
            'languages' => $object->languages,
            'headers' => [],
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof SimplifiedRequest;
    }
}
