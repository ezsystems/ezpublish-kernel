<?php
/**
 * File containing the MissingAlias class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Image\Exception;
use LogicException,
    Exception;

class MissingAlias extends LogicException
{
    public $aliasName;

    public function __construct( $aliasName, Exception $previous = null )
    {
        $this->aliasName = $aliasName;
        parent::__construct( "'Image\\Manager' has a logic error, mandatory alias '$aliasName' cannot be used", $previous );
    }
}
