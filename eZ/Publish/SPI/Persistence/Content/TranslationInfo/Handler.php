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
     * @param \eZ\Publish\SPI\Persistence\Content\TranslationInfo\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\TranslationInfo
     */
    public function createTranslationInfo( CreateStruct $createStruct );

    /**
     * loads the translation info for the given id
     *
     * @param $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\TranslationInfo
     */
    public function loadTranslationInfo( $id );

    /**
     * load translation infos for a content object
     *
     * @param $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\TranslationInfo[]
     */
    public function loadTranslationInfos( $contentId );

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

}
