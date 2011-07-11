<?php
/**
 * File containing the (content) Version class
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
class Version
{
    /**
     */
    public $versionNr;
    /**
     */
    public $modified;
    /**
     */
    public $creator;
    /**
     */
    public $created;
    /**
     */
    public $state;
    /**
     */
    public $unnamed_Content_;
    /**
     */
    public $field = array();

    public $language;

    public $fromVersionNr;
}
?>
