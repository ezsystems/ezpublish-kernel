<?php
/**
 * File containing the DirLogic class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Exception;
use ezp\Base\Exception\Logic,
    Exception as PHPException;

/**
 * DirLogic exception.
 * Thrown when a problem occurs in an operation on a directory.
 */
class DirLogic extends Logic
{
    public function __construct( $message, $code = 0, PHPException $previous = null )
    {
        parent::__construct( 'I/O Directory operation', $message, $previous );
        $this->code = $code;
    }
}
