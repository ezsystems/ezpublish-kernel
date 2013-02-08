<?php
/**
 * File containing the ContentList ValueObjectVisitor class
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
 * ContentList value object visitor
 */
class ContentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentList' ) );
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader( 'Accept-Patch', false );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'objects' )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'ContentInfo' );
        foreach ( $data->contents as $content )
        {
            $visitor->visitValueObject( $content );
        }
        $generator->endList( 'ContentInfo' );

        $generator->endObjectElement( 'ContentList' );
    }
}
