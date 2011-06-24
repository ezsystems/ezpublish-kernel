<?php
/**
 * File containing ezp\base\Exception class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */
namespace ezp\base;
class Exception extends \Exception
{
    /**
     * Original message, before escaping
     */
    public $originalMessage;

    /**
     * Constructs a new ezp\base\Exception with $message
     *
     * @param string $message
     */
    public function __construct( $message )
    {
        $this->originalMessage = $message;

        if ( PHP_SAPI === 'cli' )
        {
            parent::__construct( $message );
        }
        else
        {
            parent::__construct( htmlspecialchars( $message ) );
        }
    }
}
?>
