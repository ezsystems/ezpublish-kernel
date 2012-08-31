<?php
/**
 * File containing the Relation ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

use eZ\Publish\API\Repository\Values\Content\Relation as RelationValue;

/**
 * Relation value object visitor
 */
class Relation extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Relation' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Relation' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'objectrelation',
                 array(
                    'object' => $data->getSourceContentInfo()->id,
                    'relation' => $data->id
                 )
            )
        );
        $generator->endAttribute( 'href' );

        $generator->startObjectElement( 'SourceContent', 'ContentInfo' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'object',
                 array(
                    'object' => $data->getSourceContentInfo()->id,
                 )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'SourceContent' );

        $generator->startObjectElement( 'DestinationContent', 'ContentInfo' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'object',
                 array(
                    'object' => $data->getDestinationContentInfo()->id,
                 )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'DestinationContent' );

        $generator->startValueElement( 'RelationType', $this->getRelationTypeString( $data->type ) );
        $generator->endValueElement( 'RelationType' );

        $generator->endObjectElement( 'Relation' );
    }

    /**
     * Returns $relationType as a readable string
     *
     * @param int $relationType
     * @return string
     */
    protected function getRelationTypeString( $relationType )
    {
        switch ( $relationType )
        {
            case RelationValue::COMMON:
                return 'COMMON';
            case RelationValue::EMBED:
                return 'EMBED';
            case RelationValue::LINK:
                return 'LINK';
            case RelationValue::FIELD:
                return 'FIELD';
        }

        throw new \Exception( 'Unknown relation type ' . $relationType . '.' );
    }
}

