<?php
/**
 * File containing the ezpExtensionOptions mock class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class ezpExtensionOptions
{
    public function __construct( array $options )
    {
        if ( isset( $options['handlerParams'] ) )
        {
            $this->handlerParams = $options['handlerParams'];
        }
    }

    public $handlerParams;
}
