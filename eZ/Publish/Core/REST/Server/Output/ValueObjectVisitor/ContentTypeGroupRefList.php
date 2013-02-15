<?php
/**
 * File containing the ContentTypeGroupRefList ValueObjectVisitor class
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
 * ContentTypeGroupRefList value object visitor
 */
class ContentTypeGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentTypeGroupRefList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentTypeGroupRefList' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'groupsOfType',
                array(
                    'type' => $data->contentType->id
                )
            )
        );
        $generator->endAttribute( 'href' );

        $groupCount = count( $data->contentTypeGroups );

        $generator->startList( 'ContentTypeGroupRef' );
        foreach ( $data->contentTypeGroups as $contentTypeGroup )
        {
            $generator->startObjectElement( 'ContentTypeGroupRef', 'ContentTypeGroup' );

            $generator->startAttribute(
                'href',
                $this->urlHandler->generate(
                    'typegroup',
                    array(
                        'typegroup' => $contentTypeGroup->id
                    )
                )
            );
            $generator->endAttribute( 'href' );

            if ( $groupCount > 1 )
            {
                $generator->startHashElement( 'unlink' );

                $generator->startAttribute(
                    'href',
                    $this->urlHandler->generate(
                        'groupOfType',
                        array(
                            'type' => $data->contentType->id,
                            'group' => $contentTypeGroup->id
                        )
                    )
                );
                $generator->endAttribute( 'href' );

                $generator->startAttribute( 'method', 'DELETE' );
                $generator->endAttribute( 'method' );

                $generator->endHashElement( 'unlink' );
            }

            $generator->endObjectElement( 'ContentTypeGroupRef' );
        }
        $generator->endList( 'ContentTypeGroupRef' );

        $generator->endObjectElement( 'ContentTypeGroupRefList' );
    }
}
