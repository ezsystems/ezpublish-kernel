<?php
/**
 * Content Type group (content class group) domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage content
 */

/**
 * Group class ( Content Class Group )
 *
 * @package ezp
 * @subpackage content
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $name
 * @property-read Type[] $contentTypes
 */
namespace ezp\Content\Type;
class Group extends\ezp\Base\AbstractModel
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

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Type[]
     */
    protected $contentTypes;

    public function __construct()
    {
        $this->contentTypes = new \ezp\Base\TypeCollection( '\ezp\Content\Type\Type' );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
