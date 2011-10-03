<?php
/**
 * File containing the ezp\Content\Version interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Content,
    ezp\Content\Field\StaticCollection as FieldCollection,
    ezp\Persistence\Content\Version as VersionValue;

/**
 * This class represents the Content Version Interface
 *
 *
 * @property-read int $id
 * @property-read int $versionNo
 * @property-read mixed $contentId
 * @property-read int $status One of the STATUS_* constants
 * @property-read \ezp\Content $content
 * @property mixed $initialLanguageId
 * @property int $creatorId
 * @property int $created
 * @property int $modified
 * @property-read ContentField[] $fields An hash structure of fields
 */
interface Version
{
    /**
     * @todo taken from eZContentObjectVersion, to be redefined
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
     * Get fields of current version
     *
     * @return \ezp\Content\Field[]
     */
    public function getFields();

    /**
     * Get content that this version is attached to
     *
     * @return \ezp\Content
     */
    public function getContent();
}
?>
