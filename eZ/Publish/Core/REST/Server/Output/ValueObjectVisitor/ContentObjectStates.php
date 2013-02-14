<?php
/**
 * File containing the ContentObjectStates ValueObjectVisitor class
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
 * ContentObjectStates value object visitor
 */
class ContentObjectStates extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Common\Values\ContentObjectStates $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentObjectStates' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentObjectStates' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'ContentObjectStates' ) );

        $generator->startList( 'ObjectState' );

        foreach ( $data->states as $state )
        {
            $generator->startObjectElement( 'ObjectState' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate(
                    'objectstate',
                    array(
                        'objectstategroup' => $state->groupId,
                        'objectstate' => $state->objectState->id
                    )
                )
            );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'ObjectState' );
        }

        $generator->endList( 'ObjectState' );

        $generator->endObjectElement( 'ContentObjectStates' );
    }
}
