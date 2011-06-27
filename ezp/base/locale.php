<?php
/**
 * File containing the ezp\base\Locale class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Locale
 *
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class Locale extends \ezp\base\AbstractModel
{
    public $code;

    public function __construct( $code )
    {
        $this->code = $code;
    }
}


?>
