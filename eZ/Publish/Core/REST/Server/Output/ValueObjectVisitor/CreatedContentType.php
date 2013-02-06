<?php
/**
 * File containing the CreatedContentType ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedContentType value object visitor
 */
class CreatedContentType extends RestContentType
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedContentType $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $restContentType = $data->contentType;

        parent::visit( $visitor, $generator, $restContentType );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'type' . $this->getUrlTypeSuffix( $restContentType->contentType->status ),
                array(
                    'type' => $restContentType->contentType->id,
                )
            )
        );
        $visitor->setStatus( 201 );
    }
}
