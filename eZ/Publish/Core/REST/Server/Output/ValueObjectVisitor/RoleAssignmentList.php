<?php
/**
 * File containing the RoleAssignmentList ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values;

/**
 * RoleAssignmentList value object visitor
 */
class RoleAssignmentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'RoleAssignmentList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'RoleAssignmentList' ) );

        $generator->startAttribute(
            'href',
            $data->isGroupAssignment ?
                $this->urlHandler->generate( 'groupRoleAssignments', array( 'group' => $data->id ) ) :
                $this->urlHandler->generate( 'userRoleAssignments', array( 'user' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'RoleAssignment' );
        foreach ( $data->roleAssignments as $roleAssignment )
        {
            $visitor->visitValueObject(
                $data->isGroupAssignment ?
                    new Values\RestUserGroupRoleAssignment( $roleAssignment, $data->id ) :
                    new Values\RestUserRoleAssignment( $roleAssignment, $data->id )
            );
        }
        $generator->endList( 'RoleAssignment' );

        $generator->endObjectElement( 'RoleAssignmentList' );
    }
}
