<?php
/**
 * File containing the ContentInfo ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\UrlHandler,
    eZ\Publish\Core\REST\Common\Output\Generator,
    eZ\Publish\Core\REST\Common\Output\Visitor,
    eZ\Publish\Core\REST\Common\Output\FieldValueSerializer,

    eZ\Publish\API\Repository\Values,
    eZ\Publish\Core\REST\Server\Values\FieldDefinitionList;

/**
 * ContentInfo value object visitor
 */
class ContentType extends ContentTypeBase
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
        $contentType = $data;

        $urlTypeSuffix = $this->getUrlTypeSuffix( $contentType->status );

        $generator->startObjectElement( 'ContentType' );

        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentType' ) );
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
        $this->visitDescriptionsList( $generator, $contentType->getDescriptions() );

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

        $visitor->visitValueObject(
            new FieldDefinitionList(
                $contentType,
                $contentType->getFieldDefinitions()
            )
        );

        $generator->endObjectElement( 'ContentType' );
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
            case Values\ContentType\ContentType::STATUS_DEFINED:
                return 'DEFINED';

            case Values\ContentType\ContentType::STATUS_DRAFT:
                return 'DRAFT';

            case Values\ContentType\ContentType::STATUS_MODIFIED:
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
            case Values\Content\Location::SORT_FIELD_PATH:
                return 'PATH';
            case Values\Content\Location::SORT_FIELD_PUBLISHED:
                return 'PUBLISHED';
            case Values\Content\Location::SORT_FIELD_MODIFIED:
                return 'MODIFIED';
            case Values\Content\Location::SORT_FIELD_SECTION:
                return 'SECTION';
            case Values\Content\Location::SORT_FIELD_DEPTH:
                return 'DEPTH';
            case Values\Content\Location::SORT_FIELD_CLASS_IDENTIFIER:
                return 'CLASS_IDENTIFIER';
            case Values\Content\Location::SORT_FIELD_CLASS_NAME:
                return 'CLASS_NAME';
            case Values\Content\Location::SORT_FIELD_PRIORITY:
                return 'PRIORITY';
            case Values\Content\Location::SORT_FIELD_NAME:
                return 'NAME';
            case Values\Content\Location::SORT_FIELD_MODIFIED_SUBNODE:
                return 'MODIFIED_SUBNODE';
            case Values\Content\Location::SORT_FIELD_NODE_ID:
                return 'NODE_ID';
            case Values\Content\Location::SORT_FIELD_CONTENTOBJECT_ID:
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
            case Values\Content\Location::SORT_ORDER_ASC:
                return 'ASC';
            case Values\Content\Location::SORT_ORDER_DESC:
                return 'DESC';
        }

        throw new \RuntimeException( "Unknown default sort order: '{$defaultSortOrder}'." );
    }
}

