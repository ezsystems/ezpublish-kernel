<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

/**
 * Abstract class for XML normalization of string input.
 *
 * @deprecated since 7.4, use \EzSystems\EzPlatformRichText\eZ\RichText\Normalizer from EzPlatformRichTextBundle.
 */
abstract class Normalizer
{
    /**
     * Check if normalizer accepts given $input for normalization.
     *
     * @param string $input
     *
     * @return bool
     */
    abstract public function accept($input);

    /**
     * Normalizes given $input and returns the result.
     *
     * @param string $input
     *
     * @return string
     */
    abstract public function normalize($input);
}
