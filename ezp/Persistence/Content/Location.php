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
     */
    public $id;
    /**
     */
    public $position;
    /**
     */
    public $hidden;
    /**
     */
    public $invisible;
    /**
     */
    public $remoteId;
    /**
     */
    public $content;
    /**
     */
    public $child = array();
    /**
     */
    public $parent_10;
}
?>
