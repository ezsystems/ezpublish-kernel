<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait
{
    /**
     * @return \Symfony\Component\Serializer\SerializerInterface
     */
    public function getSerializer()
    {
        return new Serializer(
            [
                new CompoundMatcherNormalizer(),
                new SimplifiedRequestNormalizer(),
                (new PropertyNormalizer())->setIgnoredAttributes(['request', 'container', 'matcherBuilder']),
            ],
            [new JsonEncoder()]
        );
    }
}
