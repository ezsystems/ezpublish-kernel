<?php

/**
 * File containing the Content LoadStruct struct.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Load struct for mass loading content or specific content versions.
 *
 * Design implies features such as always available and language logic needs to be done in API layer, so SPI gets
 * a specifc query to deal with for the lookup that can be safely cached.
 *
 * @deprecated Not in use anymore as of v7.2.3 as it was causing slow storage engine performance on large amount of bulk loading.
 */
class LoadStruct extends ValueObject
{
    /**
     * Content's unique ID.
     *
     * @var mixed
     */
    public $id;

    /**
     * Version number for version we would like to load, current version will be assumed if null.
     *
     * TIP: On usage with content load methods, if you need to be 100% sure current version is loaded, then let this
     * stay as null. Otherwise there is a corner case possibility someone might have published a new version in-between
     * loading content info to get version number and loading content, which can result in strange reports about
     * permission errors as most users don't have version read access.
     *
     * @var int|null
     */
    public $versionNo;

    /**
     * List of language code on translated properties of returned object.
     *
     * *Should* in the future be treated as prioritized languages by storage engine, returning only the first language matched.
     *
     * @var string[]
     */
    public $languages = [];
}
