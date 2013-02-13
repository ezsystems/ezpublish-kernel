<?php
/**
 * File containing the CreatedObjectState ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedObjectState value object visitor
 */
class CreatedObjectState extends RestObjectState
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedObjectState $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->objectState );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'objectstate',
                array(
                    'objectstategroup' => $data->objectState->groupId,
                    'objectstate' => $data->objectState->objectState->id
                )
            )
        );
        $visitor->setStatus( 201 );
    }
}
