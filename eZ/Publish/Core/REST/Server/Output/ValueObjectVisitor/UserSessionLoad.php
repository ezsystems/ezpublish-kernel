<?php
/**
 * File containing the UserSessionLoad ValueObjectVisitor class
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
 * UserSessionLoad value object visitor
 */
class UserSessionLoad extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\UserSession $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Session' ) );
        $sessionHref = $this->router->generate( 'ezpublish_rest_loadSession', array( 'sessionId' => $data->sessionId ) );

        $generator->startObjectElement( 'Session' );

        $generator->startAttribute( 'href', $sessionHref );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'name', $data->sessionName );
        $generator->endValueElement( 'name' );

        $generator->startValueElement( 'identifier', $data->sessionId );
        $generator->endValueElement( 'identifier' );

        $generator->startValueElement( 'csrfToken', $data->csrfToken );
        $generator->endValueElement( 'csrfToken' );

        $generator->startObjectElement( 'User', 'User' );
        $generator->startAttribute(
            'href',
            $this->router->generate( 'ezpublish_rest_loadUser', array( 'userId' => $data->user->id ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'User' );

        $generator->endObjectElement( 'Session' );
    }
}
