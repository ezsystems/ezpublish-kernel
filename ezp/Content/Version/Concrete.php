<?php
/**
 * File containing the ezp\Content\Version\Concrete class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Version;
use ezp\Base\Model,
    ezp\Content,
    ezp\Content\Version,
    ezp\Content\Field\StaticCollection as FieldCollection,
    ezp\Persistence\Content\Version as VersionValue;

/**
 * This class represents a Concrete Content Version
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
class Concrete extends Model implements Version
{

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'versionNo' => false,
        'creatorId' => true,
        'created' => true,
        'modified' => true,
        'status' => false,
        'contentId' => false,
        'initialLanguageId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'fields' => true,
        'content' => false,
    );

    /**
     * @var \ezp\Content\Field[]
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
        $this->properties = new VersionValue(
            array(
                'contentId' => $content->id,
                'status' => self::STATUS_DRAFT,
            )
        );
        $this->content = $content;
        $this->fields = new FieldCollection( $this );
    }

    /**
     * Get fields of current version
     *
     * @return \ezp\Content\Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get content that this version is attached to
     *
     * @return \ezp\Content
     */
    public function getContent()
    {
        return $this->content;
    }
}
