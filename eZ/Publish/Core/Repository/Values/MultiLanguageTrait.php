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
trait MultiLanguageTrait
{
    /**
     * Main language.
     *
     * @var string
     */
    protected $mainLanguageCode;

    /**
     * Prioritized languages provided by user when retrieving object using API.
     *
     * @var string[]
     */
    protected $prioritizedLanguages = [];
}
