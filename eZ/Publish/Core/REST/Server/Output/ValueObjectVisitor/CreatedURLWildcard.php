<?php
/**
 * File containing the CreatedURLWildcard ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * CreatedURLWildcard value object visitor
 */
class CreatedURLWildcard extends URLWildcard
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\CreatedURLWildcard $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        parent::visit( $visitor, $generator, $data->urlWildcard );
        $visitor->setHeader(
            'Location',
            $this->urlHandler->generate(
                'urlWildcard',
                array( 'urlwildcard' => $data->urlWildcard->id )
            )
        );
        $visitor->setStatus( 201 );
    }
}
