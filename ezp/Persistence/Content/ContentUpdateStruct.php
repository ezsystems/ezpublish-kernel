<?php
/**
 * File containing the ContentUpdateStruct struct
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
class ContentUpdateStruct
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $sectionId;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var Location[]
     */
    public $newParents;

    /**
     * Location[]
     */
    public $removeLocations;

    /**
     * Field[]
     */
    public $fields = array();
}
?>
