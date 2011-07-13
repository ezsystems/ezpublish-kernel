<?php
/**
 * File containing the ContentCreateStruct struct
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
 */
class ContentCreateStruct extends \ezp\Persistence\AbstractValueObject
{
    /**
     * @var string
     * @todo Language?
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
     * @var int[]
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
}
?>
