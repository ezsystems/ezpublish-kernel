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
class ContentCreateStruct
{
    /**
     */
    public $name;
    /**
     */
    public $type;
    /**
     */
    public $sectionId;
    /**
     */
    public $ownerId;
    /**
     */
    public $parentLocation;
    /**
     */
    public $field = array();
}
?>
