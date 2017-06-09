<?php

/**
 * File containing the ObjectState class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState as APIObjectState;

/**
 * This class represents a object state value.
 *
 * @property-read mixed $id the id of the content type group
 * @property-read string $identifier the identifier of the content type group
 * @property-read int $priority the priority in the group ordering
 * @property-read string $defaultLanguageCode the default language of the object state group names and description used for fallback.
 * @property-read string[] $languageCodes the available languages
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ObjectState extends APIObjectState
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    protected $objectStateGroup;

    /**
     * Holds the collection of names with languageCode keys.
     *
     * @var string[]
     */
    protected $names = [];

    /**
     * Holds the collection of descriptions with languageCode keys.
     *
     * @var string[]
     */
    protected $descriptions = [];

    /**
     * Prioritized languages provided by user when retrieving object using API.
     *
     * @var string[]
     */
    protected $prioritizedLanguages = [];

    /**
     * The object state group this object state belongs to.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function getObjectStateGroup()
    {
        return $this->objectStateGroup;
    }

    /**
     * {@inheritdoc}.
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}.
     */
    public function getName($languageCode = null)
    {
        if (!empty($languageCode)) {
            return isset($this->names[$languageCode]) ? $this->names[$languageCode] : null;
        }

        foreach ($this->prioritizedLanguages as $prioritizedLanguageCode) {
            if (isset($this->names[$prioritizedLanguageCode])) {
                return $this->names[$prioritizedLanguageCode];
            }
        }

        return $this->names[$this->defaultLanguageCode];
    }

    /**
     * {@inheritdoc}.
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * {@inheritdoc}.
     */
    public function getDescription($languageCode = null)
    {
        if (!empty($languageCode)) {
            return isset($this->descriptions[$languageCode]) ? $this->descriptions[$languageCode] : null;
        }

        foreach ($this->prioritizedLanguages as $prioritizedLanguageCode) {
            if (isset($this->descriptions[$prioritizedLanguageCode])) {
                return $this->descriptions[$prioritizedLanguageCode];
            }
        }

        return $this->descriptions[$this->defaultLanguageCode];
    }
}
