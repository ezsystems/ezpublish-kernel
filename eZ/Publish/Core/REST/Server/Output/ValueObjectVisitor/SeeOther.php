<?php
/**
 * File containing the SeeOther ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * SeeOther Value object visitor
 */
class SeeOther extends ValueObjectVisitor
{
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $visitor->setStatus( 303 );
        $visitor->setHeader( 'Location', $data->redirectUri );
    }
}
