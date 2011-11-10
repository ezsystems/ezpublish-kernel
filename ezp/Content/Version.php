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
 * @property-read int $id
 * @property-read int $versionNo
 * @property-read mixed $contentId
 * @property-read int $status One of the STATUS_* constants
 * @property-read \ezp\Content $content
 * @property-read string[] $name Name in the different languages
 * @property-read mixed $ownerId Content's Owner id
 * @property-read bool $alwaysAvailable Content's always available flag
 * @property-read string $remoteId Content's Remote ID
 * @property-read mixed $sectionId Content' Section ID
 * @property-read mixed $typeId Content's Type ID
 * @property-read int $contentStatus Content' status, as one of the \ezp\Content::STATUS_* constants
 * @property-read \ezp\Content\Location $mainLocation Content's main location
 * @property-read \ezp\Content\Section $section Content's Section
 * @property-read \ezp\User $owner Content's Owner
 * @property-read \ezp\Content\Type $contentType Content's type
 * @property-read \ezp\Content\Location[] $locations Content's locations
 * @property-read \ezp\Content\Relation[] $relations Content's relations
 * @property-read \ezp\Content\Relation[] $reverseRelations Content's reverse relations
 * @property-read \ezp\Content\Language $initialLanguage Content's initial language
 * @property mixed $initialLanguageId
 * @property int $creatorId
 * @property int $created
 * @property int $modified
 * @property-read \ezp\Content\Field[] $fields An hash structure of fields
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
