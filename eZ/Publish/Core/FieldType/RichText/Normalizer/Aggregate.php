<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText\Normalizer;

use eZ\Publish\Core\FieldType\RichText\Normalizer;

/**
 * Aggregate normalizer converts using configured normalizers in prioritized order.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Normalizer\Aggregate from EzPlatformRichTextBundle.
 */
class Aggregate extends Normalizer
{
    /**
     * An array of normalizers, sorted by priority.
     *
     * @var \eZ\Publish\Core\FieldType\RichText\Normalizer[]
     */
    protected $normalizers = [];

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\Normalizer[] $normalizers An array of Normalizers, sorted by priority
     */
    public function __construct(array $normalizers = [])
    {
        $this->normalizers = $normalizers;
    }

    /**
     * Check if normalizer accepts given $input for normalization.
     *
     * This implementation always returns true.
     *
     * @param string $input
     *
     * @return bool
     */
    public function accept($input)
    {
        return true;
    }

    /**
     * Normalizes given $input by calling aggregated normalizers.
     *
     * @param string $input
     *
     * @return string
     */
    public function normalize($input)
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->accept($input)) {
                $input = $normalizer->normalize($input);
            }
        }

        return $input;
    }
}
