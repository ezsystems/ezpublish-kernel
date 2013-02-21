<?php
/**
 * File containing the URLWildcard ValueObjectVisitor class
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
 * URLWildcard value object visitor
 */
class URLWildcard extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\Content\URLWildcard $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'UrlWildcard' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UrlWildcard' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'urlWildcard', array( 'urlwildcard' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startAttribute( 'id', $data->id );
        $generator->endAttribute( 'id' );

        $generator->startValueElement( 'sourceUrl', $data->sourceUrl );
        $generator->endValueElement( 'sourceUrl' );

        $generator->startValueElement( 'destinationUrl', $data->destinationUrl );
        $generator->endValueElement( 'destinationUrl' );

        $generator->startValueElement( 'forward', $data->forward ? 'true' : 'false' );
        $generator->endValueElement( 'forward' );

        $generator->endObjectElement( 'UrlWildcard' );
    }
}
