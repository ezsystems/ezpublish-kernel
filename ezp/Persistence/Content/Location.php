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
 */
class Location
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
     * Materialized path.
     *
     * @var string
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
     * List of child IDs.
     *
     * @var array(mixed) Location IDs.
     */
    public $children = array();
    /**
     * Parent ID.
     *
     * @var mixed Location ID.
     */
    public $parentId;
}
?>
