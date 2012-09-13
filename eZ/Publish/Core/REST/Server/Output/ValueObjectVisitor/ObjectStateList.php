<?php
/**
 * File containing the ObjectStateList visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

use eZ\Publish\Core\REST\Common\Values\ObjectState as CommonObjectState;

/**
 * ObjectStateList value object visitor
 */
class ObjectStateList extends ValueObjectVisitor
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
        $generator->startObjectElement( 'ObjectStateList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ObjectStateList' ) );

        $generator->startAttribute( 'href', $this->urlHandler->generate( 'objectstates', array( 'objectstategroup' => $data->groupId ) ) );
        $generator->endAttribute( 'href' );

        $generator->startList( 'ObjectState' );
        foreach ( $data->states as $state )
        {
            $visitor->visitValueObject(
                new CommonObjectState( $state, $data->groupId )
            );
        }
        $generator->endList( 'ObjectState' );

        $generator->endObjectElement( 'ObjectStateList' );
    }
}
