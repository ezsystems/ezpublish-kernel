<?php
/**
 * File containing the ContentTypeGroupList ValueObjectVisitor class
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
 * ContentTypeGroupList value object visitor
 */
class ContentTypeGroupList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentTypeGroupList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentTypeGroupList' ) );
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader( 'Accept-Patch', false );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'typegroups' )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'ContentTypeGroup' );
        foreach ( $data->contentTypeGroups as $contentTypeGroup )
        {
            $visitor->visitValueObject( $contentTypeGroup );
        }
        $generator->endList( 'ContentTypeGroup' );

        $generator->endObjectElement( 'ContentTypeGroupList' );
    }
}
