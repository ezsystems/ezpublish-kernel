<?php
/**
 * File containing the ezp\content\Translation class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Content translation
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Translation extends \ezp\base\AbstractModel
{
    public function __construct( $localeCode = null )
    {
        $this->properties = array(
            "localeCode"		=> $localeCode,
            "revision"			=> 1
        );

        $this->readOnlyProperties = array(
        	"revision"			=> true
        );
    }
}
?>