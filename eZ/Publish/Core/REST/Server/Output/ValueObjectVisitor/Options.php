<?php
/**
 * File containing the CreatedPolicy ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Options value object visitor
 *
 * @todo coverage add unit test
 */
class Options extends ValueObjectVisitor
{
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $visitor->setHeader( 'Allow', implode( ',', $data->allowedMethods ) );
        $visitor->setHeader( 'Content-Length', 0 );
        $visitor->setStatus( 200 );
    }
}
