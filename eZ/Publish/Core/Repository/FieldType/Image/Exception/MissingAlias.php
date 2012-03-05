<?php
/**
 * File containing the MissingAlias class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Image\Exception;
use ezp\Base\Exception\Logic,
    Exception as PHPException;

class MissingAlias extends Logic
{
    public $aliasName;

    public function __construct( $aliasName, PHPExcetion $previous )
    {
        $this->aliasName = $aliasName;
        parent::__construct( 'Image\\Manager', "Mandatory alias '$aliasName' cannot be used", $previous );
    }
}
