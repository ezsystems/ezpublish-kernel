<?php
/**
 * File containing the Policy ValueObjectVisitor class
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
 * Policy value object visitor
 */
class Policy extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\User\Policy $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Policy' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Policy' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'PolicyUpdate' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'policy', array( 'role' => $data->roleId, 'policy' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'id', $data->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'module', $data->module );
        $generator->endValueElement( 'module' );

        $generator->startValueElement( 'function', $data->function );
        $generator->endValueElement( 'function' );

        $limitations = $data->getLimitations();
        if ( !empty( $limitations ) )
        {
            $generator->startHashElement( 'limitations' );
            $generator->startList( 'limitation' );

            foreach ( $limitations as $limitation )
            {
                $this->visitLimitation( $generator, $limitation );
            }

            $generator->endList( 'limitation' );
            $generator->endHashElement( 'limitations' );
        }

        $generator->endObjectElement( 'Policy' );
    }
}
