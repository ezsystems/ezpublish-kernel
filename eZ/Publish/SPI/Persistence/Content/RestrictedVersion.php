<?php
/**
 * File containing the RestrictedVersion class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Struct containing properties for a Version entity without its fields.
 *
 * The omission of fields is so that this struct can be used for batch
 * operations where full set of field data would be unnecessary.
 */
class RestrictedVersion extends ValueObject
{
    /**
     * Version ID.
     *
     * @var mixed
     */
    public $id;

    /**
     * Name in the different available translations
     *
     * @var string[]
     */
    public $name;

    /**
     * Version number.
     *
     * In contrast to {@link $id}, this is the version number, which only
     * increments in scope of a single Content object.
     *
     * @var int
     */
    public $versionNo;

    /**
     * @var int
     */
    public $modified;

    /**
     * Creator user ID.
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * @var int
     */
    public $created;

    /**
     * One of Version::STATUS_DRAFT, Version::STATUS_PUBLISHED, Version::STATUS_ARCHIVED
     *
     * @var int Constant.
     */
    public $status;

    /**
     * Content ID.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * @todo: Document
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * List of languages (id's) in this version
     *
     * Reflects which languages fields exists in for this version.
     *
     * @var mixed[]
     */
    public $languageIds = array();
}
