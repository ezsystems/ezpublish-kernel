<?php
/**
 * File containing the CreateStruct struct
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 */
class CreateStruct extends ValueObject
{
    /**
     * @todo Language?
     * @var string
     */
    public $name;

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
     * contentId, contentVersion and mainLocationId are allowed to be left empty
     * when used on with this struct as these values are created by the create method.
     * @todo Use custom create struct and api?
     *
     * @var \ezp\Persistence\Content\Location\CreateStruct[]
     */
    public $parentLocations = array();

    /**
     * Contains *all* fields of the object to be created.
     *
     * This attribute should contain *all* fields (in all language) of the
     * object to be created. If a field is not translateable, it may only occur
     * once. The storage layer will automatically take care, that such fields
     * are assigned to each language version.
     *
     * @var Field[]
     */
    public $fields = array();

    /**
     * @var bool Always available flag
     */
    public $alwaysAvailable = false;

    /**
     * @var string Remote identifier used as a custom identifier for the object
     */
    public $remoteId;
}
?>
