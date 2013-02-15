<?php
/**
 * File containing the CreatedContent ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedContent value object visitor
 */
class CreatedContent extends RestContent
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedContent $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->content );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'object',
                array( 'object' => $data->content->contentInfo->id )
            )
        );
        $visitor->setStatus( 201 );
    }
}
