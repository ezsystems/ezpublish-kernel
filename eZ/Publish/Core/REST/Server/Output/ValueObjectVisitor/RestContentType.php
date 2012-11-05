<?php
/**
 * File containing the RestContentType ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,

    eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType,
    eZ\Publish\Core\REST\Server\Values;

/**
 * RestContentType value object visitor
 */
class RestContentType extends RestContentTypeBase
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestContentType $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $contentType = $data->contentType;

        $urlTypeSuffix = $this->getUrlTypeSuffix( $contentType->status );
        $mediaType = $data->fieldDefinitions !== null ? 'ContentType' : 'ContentTypeInfo';

        $generator->startObjectElement( $mediaType );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( $mediaType ) );

        if ( $contentType->status === APIContentType::STATUS_DRAFT )
        {
            $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'ContentTypeUpdate' ) );
        }

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'type' . $urlTypeSuffix,
                array(
                    'type' => $contentType->id,
                )
            )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'id', $contentType->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'status', $this->serializeStatus( $contentType->status ) );
        $generator->endValueElement( 'status' );

        $generator->startValueElement( 'identifier', $contentType->identifier );
        $generator->endValueElement( 'identifier' );

        $this->visitNamesList( $generator, $contentType->getNames() );

        $descriptions = $contentType->getDescriptions();
        if ( is_array( $descriptions ) )
        {
            $this->visitDescriptionsList( $generator, $descriptions );
        }

        $generator->startValueElement( 'creationDate', $contentType->creationDate->format( 'c' ) );
        $generator->endValueElement( 'creationDate' );

        $generator->startValueElement( 'modificationDate', $contentType->modificationDate->format( 'c' ) );
        $generator->endValueElement( 'modificationDate' );

        $generator->startObjectElement( 'Creator', 'User' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'user',
                array( 'user' => $contentType->creatorId )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Creator' );

        $generator->startObjectElement( 'Modifier', 'User' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'user',
                array( 'user' => $contentType->modifierId )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Modifier' );

        $generator->startValueElement( 'remoteId', $contentType->remoteId );
        $generator->endValueElement( 'remoteId' );

        $generator->startValueElement( 'urlAliasSchema', $contentType->urlAliasSchema );
        $generator->endValueElement( 'urlAliasSchema' );

        $generator->startValueElement( 'nameSchema', $contentType->nameSchema );
        $generator->endValueElement( 'nameSchema' );

        $generator->startValueElement( 'isContainer', ( $contentType->isContainer ? 'true' : 'false' ) );
        $generator->endValueElement( 'isContainer' );

        $generator->startValueElement( 'mainLanguageCode', $contentType->mainLanguageCode );
        $generator->endValueElement( 'mainLanguageCode' );

        $generator->startValueElement( 'defaultAlwaysAvailable', ( $contentType->defaultAlwaysAvailable ? 'true' : 'false' ) );
        $generator->endValueElement( 'defaultAlwaysAvailable' );

        $generator->startValueElement( 'defaultSortField', $this->serializeSortField( $contentType->defaultSortField ) );
        $generator->endValueElement( 'defaultSortField' );

        $generator->startValueElement( 'defaultSortOrder', $this->serializeSortOrder( $contentType->defaultSortOrder ) );
        $generator->endValueElement( 'defaultSortOrder' );

        if ( $data->fieldDefinitions !== null )
        {
            $visitor->visitValueObject(
                new Values\FieldDefinitionList(
                    $contentType,
                    $data->fieldDefinitions
                )
            );
        }

        $generator->endObjectElement( $mediaType );
    }
}
