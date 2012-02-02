<?php
/**
 * File containing the Content class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence;

/**
 */
class Content extends ValueObject
{
    /**
     * Publication status constants
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * @var int
     */
    public $id;

    /**
     * One of Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED
     *
     * @var int Constant.
     */
    public $status;

    /**
     * @var int
     */
    public $typeId;

    /**
     * @var int
     */
    public $sectionId;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * Content modification date
     * @var int Unix timestamp
     */
    public $modified;

    /**
     * Content publication date
     * @var int Unix timestamp
     */
    public $published;

    /**
     * Current Version number
     *
     * Contains the current version number of the published version.
     * If no published version exists, last draft is used, and if published version is removed, current version is
     * set to latest modified version.
     *
     * Eg: When creating a new Content object current version will point to version 1 even if it is a draft.
     *
     * @var int
     */
    public $currentVersionNo;

    /**
     * The loaded version
     *
     * The Version, containing version information and all
     * {@link \eZ\Publish\SPI\Persistence\Content\Field}s in this version (in all languages).
     * Non-translatable fields will only occur once!
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Version
     */
    public $version;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public $locations = array();

    /**
     * @var boolean Always available flag
     */
    public $alwaysAvailable = false;

    /**
     * Remote identifier used as a custom identifier for the object
     * @var string
     */
    public $remoteId;

    /**
     * Language id the content was initially created in
     * @var mixed
     */
    public $initialLanguageId;
}
?>
