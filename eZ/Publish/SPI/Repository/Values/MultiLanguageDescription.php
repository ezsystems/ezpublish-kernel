<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Repository\Values;

/**
 * This is the interface for all ValueObjects implementing translated description logic.
 *
 * Provides a uniform way for API consuming logic to generate translated description labels
 * for API objects.
 *
 * @todo Move to API, Repository is not a SPI concept.
 */
interface MultiLanguageDescription
{
    /**
     * Return the human readable description in all provided languages.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions();

    /**
     * Return the human readable description of the domain object in a given language.
     *
     * - If $languageCode is defined, return if available, otherwise null
     * - If not, pick it using prioritized language (if provided to api on object retrieval),
     *   otherwise in main language
     *
     * @param string|null $languageCode
     *
     * @return string|null The description for a given language, or null if $languageCode is not set
     *         or does not exist.
     */
    public function getDescription($languageCode = null);
}
