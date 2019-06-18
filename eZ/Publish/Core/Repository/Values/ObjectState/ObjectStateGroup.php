<?php

/**
 * File containing the ObjectStateGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup as APIObjectStateGroup;

/**
 * This class represents an object state group value.
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read string $defaultLanguageCode, the default language code of the object state group names and description used for fallback.
 * @property-read string[] $languageCodes the available languages
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ObjectStateGroup extends APIObjectStateGroup
{
    /**
     * Human readable names of object state group.
     *
     * @var string[]
     */
    protected $names = [];

    /**
     * Human readable descriptions of object state group.
     *
     * @var string[]
     */
    protected $descriptions = [];

    /**
     * This method returns the human readable name in all provided languages
     * of the content type.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * This method returns the name of the content type in the given language.
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    public function getName($languageCode)
    {
        if (!isset($this->names[$languageCode])) {
            return null;
        }

        return $this->names[$languageCode];
    }

    /**
     * This method returns the human readable description of the content type.
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * This method returns the name of the content type in the given language.
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription($languageCode)
    {
        if (!isset($this->descriptions[$languageCode])) {
            return null;
        }

        return $this->descriptions[$languageCode];
    }
}
