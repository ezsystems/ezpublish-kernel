<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\TranslationInfo\Handler interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content\TranslationInfo;

/**
 * this interface provides contains methods for handling translation infos
 * @package eZ\Publish\SPI\Persistence\Content\TranslationInfo
 */
interface Handler {

    /**
     * creates a translation info
     *
     * @param CreateStruct $createStruct
     *
     * @return mixed
     */
    public function createTranslationInfo( CreateStruct $createStruct );

    /**
     * loads the translation info for the given id
     *
     * @param $id
     *
     * @return mixed
     */
    public function loadTranslationInfo( $id );

    /**
     * removes the translation info for the given id
     *
     * the caller ensures that the translation info exists
     *
     * @param $id
     *
     * @return mixed
     */
    public function removeTranslationInfo( $id );

    /**
     * finds translation infos
     *
     * the filters may contain the following keys:
     * destination: returns all translation infos containing the given language code in the destinationLanguageCode
     * source: returns all translation infos containing the given language code in the sourceLanguageCode
     * destinationVersion: returns all translation infos containing the version number in destinationVersion
     * sourceVersion: returns all translation infos containing the version number in sourceVersion
     * if more than one key is given the results are intersected. Unions have to be built manually by calling this
     * method multiple times.
     *
     * @param $contentId
     * @param array $filters
     *
     * @return mixed
     */
    public function findTranslationInfos( $contentId, array $filters = array() );
}