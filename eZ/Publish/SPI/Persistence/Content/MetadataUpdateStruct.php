<?php
/**
 * File containing the MetadataUpdateStruct struct
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class MetadataUpdateStruct extends ValueObject
{
    /**
     * If set, this value changes the content's owner ID.
     *
     * @var int
     */
    public $ownerId;

    /**
     * If set, will change the content's "always-available" name
     *
     * @var string
     */
    public $name;

    /**
     * If set this value overrides the publication date of the content.
     * Unix timestamp.
     *
     * @var int
     */
    public $publicationDate;

    /**
     * If set this value overrides the modification date.
     * Unix timestamp.
     *
     * @var int
     */
    public $modificationDate;

    /**
     * If set, the content's main language will be changed.
     *
     * @var int
     */
    public $mainLanguageId;

    /**
     * If set, this value will change the always available flag.
     *
     * @var boolean
     */
    public $alwaysAvailable;

    /**
     * If set, this value will change the content's remote ID.
     *
     * @var string
     */
    public $remoteId;
}
