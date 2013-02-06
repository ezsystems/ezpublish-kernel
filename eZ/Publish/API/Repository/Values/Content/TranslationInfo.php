<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\TranslationInfo class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 *
 * 5.x this class is used for reading and writing translation informations into the repository
 * @property-read ContentInfo $contentInfo returns $sourceVersionInfo->getContentInfo()
 */
class TranslationInfo extends ValueObject
{
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
     * the source version this translation is based on
     *
     * @var VersionInfo
     */
    public $srcVersionInfo;

    /**
     * the destination version this translation is placed in
     *
     * @var VersionInfo
     */
    public $destinationVersionInfo;
}
