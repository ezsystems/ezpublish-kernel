<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

/**
 * Helper functions available in XSL documents.
 */
class XSLTProcessorFunctions
{
    const URL_PATTERN = '%^((ezlocation|ezurl|ezcontent):\/\/\d+(#[^\s]+)?)|(#[^\s]+)|(?:(?:https?|ftps?)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu';

    /**
     * Return true if URL is valid.
     *
     * Validates URL link using the diegoperini regex found at https://mathiasbynens.be/demo/url-regex
     * aka. https://gist.github.com/dperini/729294#gistcomment-15527
     *
     * @param string $value
     * @return bool
     */
    public static function isValidUrl($value)
    {
        return (bool)preg_match(self::URL_PATTERN, $value);
    }
}
