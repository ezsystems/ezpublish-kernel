<?php
/**
 * File containing the Role ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Visitor;

/**
 * Role value object visitor
 */
class Role extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param Visitor $visitor
     * @param Generator $generator
     * @param mixed $data
     * @return void
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startElement( 'Role' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Role' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'role', array( 'role' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        $generator->startElement( 'PolicyList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'policies', array( 'role' => $data->id ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endElement( 'PolicyList' );

        $generator->endElement( 'Role' );
    }
}

