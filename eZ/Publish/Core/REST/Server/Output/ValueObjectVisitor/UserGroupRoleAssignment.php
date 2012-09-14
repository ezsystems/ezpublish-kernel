<?php
/**
 * File containing the UserGroupRoleAssignment ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;

/**
 * User group role assignment value object visitor
 */
class UserGroupRoleAssignment extends ValueObjectVisitor
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
        $generator->startObjectElement( 'RoleAssignment' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'RoleAssignment' ) );

        $role = $data->getRole();

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'groupRoleAssignment', array(
                    'group' => $data->getUserGroup()->getVersionInfo()->getContentInfo()->id,
                    'role' => $role->id
                )
            )
        );
        $generator->endAttribute( 'href' );

        $roleLimitation = $data->getRoleLimitation();
        if ( $roleLimitation instanceof RoleLimitation )
        {
            $this->visitLimitation( $generator, $roleLimitation );
        }

        $generator->startObjectElement( 'Role' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'role', array( 'role' => $role->id ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Role' );

        $generator->endObjectElement( 'RoleAssignment' );
    }
}
