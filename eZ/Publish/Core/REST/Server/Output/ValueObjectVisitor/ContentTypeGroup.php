<?php
/**
 * File containing the ContentTypeGroup ValueObjectVisitor class
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
 * ContentTypeGroup value object visitor
 */
class ContentTypeGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentTypeGroup' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentTypeGroup' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'ContentTypeGroupInput' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'typegroup', array( 'typegroup' => $data->id ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'id', $data->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        $generator->startValueElement( 'created', $data->creationDate->format( 'c' ) );
        $generator->endValueElement( 'created' );

        $generator->startValueElement( 'modified', $data->modificationDate->format( 'c' ) );
        $generator->endValueElement( 'modified' );

        $generator->startObjectElement( 'Creator', 'User' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'user', array( 'user' => $data->creatorId ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Creator' );

        $generator->startObjectElement( 'Modifier', 'User' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'user', array( 'user' => $data->modifierId ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Modifier' );

        $generator->startObjectElement( 'ContentTypes', 'ContentTypeInfoList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'grouptypes', array( 'typegroup' => $data->id ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'ContentTypes' );

        $generator->endObjectElement( 'ContentTypeGroup' );
    }
}
