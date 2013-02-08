<?php
/**
 * File containing the UserGroupRefList ValueObjectVisitor class
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
 * UserGroupRefList value object visitor
 */
class UserGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\UserGroupRefList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'UserGroupRefList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UserGroupRefList' ) );
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader( 'Accept-Patch', false );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

        $groupCount = count( $data->userGroups );

        $generator->startList( 'UserGroup' );
        foreach ( $data->userGroups as $userGroup )
        {
            $generator->startObjectElement( 'UserGroup' );

            $generator->startAttribute(
                'href',
                $this->urlHandler->generate(
                    'group',
                    array(
                        'group' => rtrim( $userGroup->mainLocation->pathString, '/' )
                    )
                )
            );
            $generator->endAttribute( 'href' );

            if ( $data->userId !== null && $groupCount > 1 )
            {
                $generator->startHashElement( 'unassign' );

                $generator->startAttribute(
                    'href',
                    $this->urlHandler->generate(
                        'userGroup',
                        array(
                            'user' => $data->userId,
                            'group' => '/' . $userGroup->mainLocation->path[count( $userGroup->mainLocation->path ) - 1]
                        )
                    )
                );
                $generator->endAttribute( 'href' );

                $generator->startAttribute( 'method', 'DELETE' );
                $generator->endAttribute( 'method' );

                $generator->endHashElement( 'unassign' );
            }

            $generator->endObjectElement( 'UserGroup' );
        }
        $generator->endList( 'UserGroup' );

        $generator->endObjectElement( 'UserGroupRefList' );
    }
}
