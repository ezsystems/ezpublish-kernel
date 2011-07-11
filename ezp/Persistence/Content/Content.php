<?php
/**
 * File containing the Content class
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
     * List containing the loaded version.
     *
     * List with only a single Version, containing version information and all 
     * {@link Field}s in this version (in all languages). Non-translateable 
     * fields will only occur once!
     *
     * @var array(Version)
     */
    public $versionInfos = array();
    /**
     */
    public $location = array();
}
?>
