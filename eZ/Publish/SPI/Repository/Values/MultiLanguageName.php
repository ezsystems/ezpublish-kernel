<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Repository\Values;

/**
 * This is the interface for all ValueObjects implementing translated name logic.
 *
 * Provides a uniform way for API consuming logic to generate translated names / labels
 * for API objects.
 * Language logic is meant to also be used for description, fields, ... lookup as well.
 *
 * @todo Move to API, Repository is not a SPI concept.
 */
interface MultiLanguageName
{
    /**
     * Return the human readable name in all provided languages.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames();

    /**
     * Return the name of the domain object in a given language.
     *
     * - If $languageCode is defined,
     *      return if available, otherwise null
     * - If not, pick using the following languages codes when applicable:
     *      1. Prioritized languages (if provided to api on object retrieval)
     *      2. Main language if object is $alwaysAvailable
     *      3. Fallback to return in initial (version objects) or main language
     *
     * @param string|null $languageCode
     *
     * @return string|null The name for a given language, or null if $languageCode is not set
     *         or does not exist.
     */
    public function getName($languageCode = null);
}
