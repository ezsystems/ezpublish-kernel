<?php
/**
 * File containing the RestContentType ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\UrlHandler,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,

    eZ\Publish\API\Repository\Values\Content\Location as APILocation,
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
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $contentType = $data->contentType;

        $urlTypeSuffix = $this->getUrlTypeSuffix( $contentType->status );
        $mediaType = $data->fieldDefinitions !== null ? 'ContentType' : 'ContentTypeInfo';

        $generator->startObjectElement( $mediaType );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( $mediaType ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'ContentTypeUpdate' ) );

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

        $generator->startValueElement( 'defaultSortField', $this->serializeDefaultSortField( $contentType->defaultSortField ) );
        $generator->endValueElement( 'defaultSortField' );

        $generator->startValueElement( 'defaultSortOrder', $this->serializeDefaultSortOrder( $contentType->defaultSortOrder ) );
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

    /**
     * Serializes the given $contentTypeStatus to a string representation
     *
     * @param int $contentTypeStatus
     * @return string
     */
    protected function serializeStatus( $contentTypeStatus )
    {
        switch ( $contentTypeStatus )
        {
            case APIContentType::STATUS_DEFINED:
                return 'DEFINED';

            case APIContentType::STATUS_DRAFT:
                return 'DRAFT';

            case APIContentType::STATUS_MODIFIED:
                return 'MODIFIED';
        }

        throw new \RuntimeException( "Unknown content type status: '{$contentTypeStatus}'." );
    }

    /**
     * Serializes the given $defaultSortField to a string representation
     *
     * @param int $defaultSortField
     * @return string
     */
    protected function serializeDefaultSortField( $defaultSortField )
    {
        switch ( $defaultSortField )
        {
            case APILocation::SORT_FIELD_PATH:
                return 'PATH';
            case APILocation::SORT_FIELD_PUBLISHED:
                return 'PUBLISHED';
            case APILocation::SORT_FIELD_MODIFIED:
                return 'MODIFIED';
            case APILocation::SORT_FIELD_SECTION:
                return 'SECTION';
            case APILocation::SORT_FIELD_DEPTH:
                return 'DEPTH';
            case APILocation::SORT_FIELD_CLASS_IDENTIFIER:
                return 'CLASS_IDENTIFIER';
            case APILocation::SORT_FIELD_CLASS_NAME:
                return 'CLASS_NAME';
            case APILocation::SORT_FIELD_PRIORITY:
                return 'PRIORITY';
            case APILocation::SORT_FIELD_NAME:
                return 'NAME';
            case APILocation::SORT_FIELD_MODIFIED_SUBNODE:
                return 'MODIFIED_SUBNODE';
            case APILocation::SORT_FIELD_NODE_ID:
                return 'NODE_ID';
            case APILocation::SORT_FIELD_CONTENTOBJECT_ID:
                return 'CONTENTOBJECT_ID';
        }

        throw new \RuntimeException( "Unknown default sort field: '{$defaultSortField}'." );
    }

    /**
     * Serializes the given $defaultSortOrder to a string representation
     *
     * @param int $defaultSortOrder
     * @return string
     */
    protected function serializeDefaultSortOrder( $defaultSortOrder )
    {
        switch ( $defaultSortOrder )
        {
            case APILocation::SORT_ORDER_ASC:
                return 'ASC';
            case APILocation::SORT_ORDER_DESC:
                return 'DESC';
        }

        throw new \RuntimeException( "Unknown default sort order: '{$defaultSortOrder}'." );
    }
}
