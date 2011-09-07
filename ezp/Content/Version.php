<?php
/**
 * File containing the ezp\Content\Version class.
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
 * This class represents a Content Version
 *
 *
 * @property-read int $id
 * @property-read int $versionNo
 * @property-read mixed $contentId
 * @property-read int $state
 * @property-read \ezp\Content $content
 * @property int $creatorId
 * @property int $created
 * @property int $modified
 * @property-read ContentField[] $fields An hash structure of fields
 */
class Version extends Model
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
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'versionNo' => false,
        'creatorId' => true,
        'created' => true,
        'modified' => true,
        "status" => false,
        'content' => false,
        "contentId" => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
    );

    /**
     * @var Field[]
     */
    protected $fields;

    /**
     * Content object this version is attached to.
     *
     * @var Content
     */
    protected $content;

    /**
     * Create content version based on content and content type fields objects
     *
     * @param Content $content
     */
    public function __construct( Content $content )
    {
        $this->properties = new VersionValue( array(
            'contentId' => $content->id,
            'status' => self::STATUS_DRAFT,
        ) );
        $this->content = $content;
        $this->fields = new FieldCollection( $this );
    }

    /**
     * Get fields of current version
     *
     * @return \ezp\Content\Field[]
     */
    protected function getFields()
    {
        return $this->fields;
    }

    /**
     * Clones the version
     *
     * @return void
     */
    public function __clone()
    {
        $this->properties->id = false;
    }
}
?>
