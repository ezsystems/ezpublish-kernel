<?php
/**
 * File containing Type class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Base\Model,
    ezp\Base\TypeCollection;

/**
 * Type class ( Content Class )
 *
 *
 * @property-read int $id
 * @property-read int $version
 * @property-read string $identifier
 * @property-read Content[] $contentObjects
 * @property-read Field[] $fields
 * @property-read Group[] $groups
 */
class Type extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'version' => false,
        'identifier' => true,
        'contentObjects' => false,
        'groups' => true,
        'fields' => true,
    );

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
     * @var Field[]
     */
    protected $fields;

    /**
     * @var \ezp\Content[]
     */
    protected $contentObjects;

    /**
     * @var Group[]
     */
    protected $groups;

    public function __construct()
    {
        $this->groups = new TypeCollection( 'ezp\\Content\\Type\\Group' );
        $this->fields = new TypeCollection( 'ezp\\Content\\Type\\Field' );
        $this->contentObjects = new TypeCollection( 'ezp\\Content' );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }
}
?>
