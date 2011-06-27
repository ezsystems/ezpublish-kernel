<?php
/**
 * File containing ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * ContentType class ( Content Class )
 *
 * @package ezp
 * @subpackage content
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $identifier
 * @property-read Content[] $contentObjects
 * @property-read ContentTypeField[] $fields
 * @property-read ContentTypeGroup[] $groups
 */
namespace ezp\content;
class ContentType extends \ezp\base\AbstractModel
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'identifier' => true,
        'contentObjects' => false,
        'groups' => true,
        'fields' => true,
    );

    public function __construct()
    {
        $this->groups = new \ezp\base\TypeCollection( '\ezp\content\ContentTypeGroup' );
        $this->fields = new \ezp\base\TypeCollection( '\ezp\content\ContentTypeField' );
        $this->contentObjects = new \ezp\base\TypeCollection( '\ezp\content\Content' );
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    public $identifier;

    /**
     * @var ContentTypeField[]
     */
    protected $fields;

    /**
     * @var Content[]
     */
    protected $contentObjects;

    /**
     * @var ContentTypeGroup[]
     */
    protected $groups;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }
}
?>
