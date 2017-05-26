<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\MultiLanguageValueBase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values;

/**
 * This is the base class for all classes providing translated description logic.
 *
 * Provides a uniform way for API consuming logic to generate translated description labels for API objects.
 */
abstract class MultiLanguageValueDescriptionBase extends MultiLanguageValueNameBase
{
    /**
     * This method returns the human readable description in all provided languages.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getDescriptions();

    /**
     * This method returns the human readable description of the domain object in a given language.
     *
     * - If $languageCode is defined, return if available, otherwise null
     * - If not pick using prioritized language (if provided to api on object retrieval), otherwise in main language
     *
     * @param string|null $languageCode
     *
     * @return string The description for a given language, or null if $languageCode is set and does not exist.
     */
    abstract public function getDescription($languageCode = null);
}
