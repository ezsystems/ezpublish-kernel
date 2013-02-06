<?php
/**
 * File containing the URLAliasRefList ValueObjectVisitor class
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
 * URLAliasRefList value object visitor
 */
class URLAliasRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\URLAliasRefList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'UrlAliasRefList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'UrlAliasRefList' ) );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

        $generator->startList( 'UrlAlias' );
        foreach ( $data->urlAliases as $urlAlias )
        {
            $generator->startObjectElement( 'UrlAlias' );

            $generator->startAttribute(
                'href',
                $this->urlHandler->generate( 'urlAlias', array( 'urlalias' => $urlAlias->id ) )
            );
            $generator->endAttribute( 'href' );

            $generator->endObjectElement( 'UrlAlias' );
        }
        $generator->endList( 'UrlAlias' );

        $generator->endObjectElement( 'UrlAliasRefList' );
    }
}
