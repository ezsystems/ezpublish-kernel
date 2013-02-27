<?php
/**
 * File containing the ContentTypeCreateStruct visitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ContentTypeCreateStruct value object visitor
 */
class ContentTypeCreateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     */
    public function visit( Visitor $visitor, Generator $generator, $contentTypeCreateStruct )
    {
        $generator->startObjectElement( "ContentTypeCreate" );
        $visitor->setHeader( "Content-Type", $generator->getMediaType( "ContentTypeCreate" ) );

        $generator->startValueElement( "identifier", $contentTypeCreateStruct->identifier );
        $generator->endValueElement( "identifier" );

        $generator->startValueElement( "remoteId", $contentTypeCreateStruct->remoteId );
        $generator->endValueElement( "remoteId" );

        $generator->startValueElement( "urlAliasSchema", $contentTypeCreateStruct->urlAliasSchema );
        $generator->endValueElement( "urlAliasSchema" );

        $generator->startValueElement( "nameSchema", $contentTypeCreateStruct->nameSchema );
        $generator->endValueElement( "nameSchema" );

        $generator->startValueElement( "isContainer", ( $contentTypeCreateStruct->isContainer ? "true" : "false" ) );
        $generator->endValueElement( "isContainer" );

        $generator->startValueElement( "mainLanguageCode", $contentTypeCreateStruct->mainLanguageCode );
        $generator->endValueElement( "mainLanguageCode" );

        $generator->startValueElement( "defaultAlwaysAvailable", ( $contentTypeCreateStruct->defaultAlwaysAvailable ? "true" : "false" ) );
        $generator->endValueElement( "defaultAlwaysAvailable" );

        $generator->startValueElement( "defaultSortField", $this->serializeSortField( $contentTypeCreateStruct->defaultSortField ) );
        $generator->endValueElement( "defaultSortField" );

        $generator->startValueElement( "defaultSortOrder", $this->serializeSortOrder( $contentTypeCreateStruct->defaultSortOrder ) );
        $generator->endValueElement( "defaultSortOrder" );

        if ( !empty( $contentTypeCreateStruct->names ) )
        {
            $generator->startHashElement( "names" );
            $generator->startList( 'value' );
            foreach ( $contentTypeCreateStruct->names as $languageCode => $name )
            {
                $generator->startValueElement( "value", $name, array( "languageCode" => $languageCode ) );
                $generator->endValueElement( "value" );
            }
            $generator->endList( 'value' );
            $generator->endHashElement( "names" );
        }

        if ( !empty( $contentTypeCreateStruct->descriptions ) )
        {
            $generator->startHashElement( "descriptions" );
            $generator->startList( 'value' );
            foreach ( $contentTypeCreateStruct->descriptions as $languageCode => $description )
            {
                $generator->startValueElement( "value", $description, array( "languageCode" => $languageCode ) );
                $generator->endValueElement( "value" );
            }
            $generator->endList( 'value' );
            $generator->endHashElement( "descriptions" );
        }

        if ( $contentTypeCreateStruct->creationDate !== null )
        {
            $generator->startValueElement( "modificationDate", $contentTypeCreateStruct->creationDate->format( "c" ) );
            $generator->endValueElement( "modificationDate" );
        }

        if ( $contentTypeCreateStruct->creatorId !== null )
        {
            $generator->startObjectElement( "User" );
            $generator->startAttribute( "href", $contentTypeCreateStruct->creatorId );
            $generator->endAttribute( "href" );
            $generator->endObjectElement( "User" );
        }

        if ( !empty( $contentTypeCreateStruct->fieldDefinitions ) )
        {
            $generator->startHashElement( "FieldDefinitions" );
            $generator->startList( "FieldDefinition" );
            foreach ( $contentTypeCreateStruct->fieldDefinitions as $fieldDefinitionCreateStruct )
            {
                $visitor->visitValueObject( $fieldDefinitionCreateStruct );
            }
            $generator->endList( "FieldDefinition" );
            $generator->endHashElement( "FieldDefinitions" );
        }

        $generator->endObjectElement( "ContentTypeCreate" );
    }
}
