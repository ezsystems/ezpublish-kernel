<?php
/**
 * File containing the Location class
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
 * @todo Add missing attributes (OMS).
 * @todo Create a Location\LocationUpdateStruct as a copy of this class with
 *       all "unsafe" properties removed (OMS).
 */
class Location extends \ezp\Persistence\AbstractValueObject
{
    /**
     * Location ID.
     *
     * @var mixed Location ID.
     */
    public $id;

    /**
     * Location position.
     *
     * Position of the Location among its siblings.
     *
     * @var int
     */
    public $position;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var bool
     */
    public $invisible;

    /**
     * Remote ID.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * ID of the corresponding {@link Content}.
     *
     * @var mixed Content ID.
     */
    public $contentId;

    /**
     * Parent ID.
     *
     * @var mixed Location ID.
     */
    public $parentId;
}
?>
