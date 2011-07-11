<?php
/**
 * File containing the Content class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence;

/**
 * @package ezp
 * @subpackage persistence
 */
class Content
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
    public $id;

    /**
     * @var Content\Version[]
     */
    public $versionInfo = array();

    /**
     * @var Content\Location[]
     */
    public $location = array();
}
?>
