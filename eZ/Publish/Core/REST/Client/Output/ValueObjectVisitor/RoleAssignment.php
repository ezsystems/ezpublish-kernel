<?php
/**
 * File containing the RoleAssignment ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * RoleAssignment value object visitor
 */
class RoleAssignment extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'RoleAssignInput' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'RoleAssignInput' ) );

        $generator->startObjectElement( 'Role' );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'role', array( 'role' => $data->getRole()->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->endObjectElement( 'Role' );

        $roleLimitation = $data->getRoleLimitation();
        if ( $roleLimitation !== null )
        {
            $visitor->visitValueObject( $roleLimitation );
        }

        $generator->endObjectElement( 'RoleAssignInput' );
    }
}
