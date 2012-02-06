<?php
/**
 * File containing the (content) Version class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Struct containing properties for a Version entity.
 */
class Version extends ValueObject
{
    /**
     * Version status constants
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_PENDING = 2;
    const STATUS_ARCHIVED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_INTERNAL_DRAFT = 5;
    const STATUS_REPEAT = 6;
    const STATUS_QUEUED = 7;

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

    /**
     * Loaded content fields in this version.
     *
     * Contains all fields for all languages of this version. Fields which are
     * not translatable wil only be contained once.
     *
     * @var array(Field)
     */
    public $fields = array();
}
