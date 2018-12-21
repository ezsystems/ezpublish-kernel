<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values;

/**
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
trait MultiLanguageNameTrait
{
    /**
     * Holds the collection of names with languageCode keys.
     *
     * @var string[]
     */
    protected $names = [];

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}
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

        return isset($this->names[$this->mainLanguageCode])
            ? $this->names[$this->mainLanguageCode]
            : reset($this->names);
    }
}
