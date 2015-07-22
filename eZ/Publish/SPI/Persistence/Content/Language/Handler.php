<?php

/**
 * File containing the Language Handler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Language;

use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Language Handler interface.
 */
interface Handler
{
    /**
     * Create a new language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function create(CreateStruct $struct);

    /**
     * Update language.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language $struct
     */
    public function update(Language $struct);

    /**
     * Get language by id.
     *
     * @param mixed $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function load($id);

    /**
     * Get language by Language Code (eg: eng-GB).
     *
     * @param string $languageCode
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language could not be found by $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    public function loadByLanguageCode($languageCode);

    /**
     * Get all languages.
     *
     * Return list of languages where key of hash is language code.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    public function loadAll();

    /**
     * Delete a language.
     *
     * @throws \LogicException If language could not be deleted
     *
     * @param mixed $id
     */
    public function delete($id);
}
