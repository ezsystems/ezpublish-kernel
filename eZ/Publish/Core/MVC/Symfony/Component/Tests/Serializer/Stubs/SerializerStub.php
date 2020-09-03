<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Tests\Serializer\Stubs;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class SerializerStub implements SerializerInterface, NormalizerInterface
{
    public function serialize($data, $format, array $context = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function deserialize($data, $type, $format, array $context = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        if (is_array($object)) {
            $result = [];
            foreach ($object as $key => $value) {
                $result[$key] = $this->normalize($value, $format, $context);
            }

            return $result;
        }

        if ($object instanceof MatcherStub) {
            return [
                'data' => $object->getData(),
            ];
        }

        return $object;
    }

    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}
