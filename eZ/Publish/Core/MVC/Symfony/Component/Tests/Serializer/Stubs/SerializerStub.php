<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
        return $object;
    }

    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}
