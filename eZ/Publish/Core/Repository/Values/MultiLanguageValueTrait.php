<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\MultiLanguageValueTrait trait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values;

/**
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
trait MultiLanguageValueTrait
{
    /**
     * Holds the collection of names with languageCode keys.
     *
     * @var string[]
     */
    protected $names;

    /**
     * Holds the collection of descriptions with languageCode keys.
     *
     * @var string[]
     */
    protected $descriptions;

    /**
     * Main language.
     *
     * @var string
     */
    protected $mainLanguageCode;

    /**
     * Prioritized languages provided by user when retrieving object using API.
     *
     * @internal
     * @var string[]
     */
    protected $prioritizedLanguages = [];

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

        foreach ($this->prioritizedLanguageCodes as $prioritizedLanguageCode) {
            if (isset($this->names[$prioritizedLanguageCode])) {
                $this->names[$prioritizedLanguageCode];
            }
        }

        return $this->names[$this->mainLanguageCode];
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

        foreach ($this->prioritizedLanguageCodes as $prioritizedLanguageCode) {
            if (isset($this->descriptions[$prioritizedLanguageCode])) {
                $this->descriptions[$prioritizedLanguageCode];
            }
        }

        return $this->descriptions[$this->mainLanguageCode];
    }
}
