<?php
/**
 * Content Type group (content class group) domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * ContentTypeGroup class ( Content Class Group )
 *
 * @package ezp
 * @subpackage content
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $name
 * @property-read ContentType[] $contentTypes
 */
namespace ezp\content;
class ContentTypeGroup extends\ezp\base\AbstractModel
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'id' => false,
        'version' => false,
        'name' => false,
        //'identifier' => true,
        'contentTypes' => false,
    );

    public function __construct()
    {
        $this->contentTypes = new \ezp\base\TypeCollection( '\ezp\content\ContentType' );
    }

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ContentType[]
     */
    protected $contentTypes;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
