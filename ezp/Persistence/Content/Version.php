<?php
/**
 * File containing the (content) Version class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;

/**
 * @package ezp
 * @subpackage persistence_content
 * @todo Add a restricted VersionInfo struct, which is returned by the {@link
 *       ContentHandler->listVersions()} method.
 */
class Version
{
    /**
     * Version ID.
     *
     * @var mixed
     */
    public $versionId;

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
     * DRAFT, PUBLISHED, ARCHIVED.
     *
     * @var int Constant.
     */
    public $state;

    /**
     * Content ID.
     *
     * @var mixed
     */
    public $contentId;

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
?>
