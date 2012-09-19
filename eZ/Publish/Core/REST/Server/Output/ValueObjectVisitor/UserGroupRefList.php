<?php
/**
 * File containing the UserGroupRefList ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'UserGroupRefList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UserGroupRefList' ) );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

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

            $generator->endObjectElement( 'UserGroup' );
        }
        $generator->endList( 'UserGroup' );

        $generator->endObjectElement( 'UserGroupRefList' );
    }
}
