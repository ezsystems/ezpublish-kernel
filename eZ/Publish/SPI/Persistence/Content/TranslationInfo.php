<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\TranslationInfo class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\Content;

/**
 * This class is used to store information about relations between versions
 * which occur when the user translates the content.
 *
 * @package eZ\Publish\SPI\Persistence\Content
 */
class TranslationInfo
{

    /**
     * the unique id of the translation info
     *
     * @var mixed
     */
    public $id;

    /**
     * the language code of the source language of the translation
     *
     * @var string
     */
    public $sourceLanguageCode;

    /**
     * the language code of the destination language of the translation
     *
     * @var string
     */
    public $destinationLanguageCode;

    /**
     * the source version id this translation is based on
     *
     * @var mixed
     */
    public $srcVersionId;

    /**
     * the destination version id this translation is placed in
     *
     * @var mixed
     */
    public $destinationVersionId;
}