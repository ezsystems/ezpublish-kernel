<?php

/**
 * File containing the LocaleConverterInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Locale;

/**
 * Interface for locale converters.
 * eZ Publish uses <ISO639-2/B>-<ISO3166-Alpha2> locale format (mostly, some supported locales being out of this format, e.g. cro-HR).
 * Symfony uses the standard POSIX locale format (<ISO639-1>_<ISO3166-Alpha2>), which is supported by Intl PHP extension.
 *
 * Locale converters are meant to convert in those 2 formats back and forth.
 */
interface LocaleConverterInterface
{
    /**
     * Converts a locale in eZ Publish internal format to POSIX format.
     * Returns null if conversion cannot be made.
     *
     * @param string $ezpLocale
     *
     * @return string|null
     */
    public function convertToPOSIX($ezpLocale);

    /**
     * Converts a locale in POSIX format to eZ Publish internal format.
     * Returns null if conversion cannot be made.
     *
     * @param string $posixLocale
     *
     * @return string|null
     */
    public function convertToEz($posixLocale);
}
