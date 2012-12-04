<?php
/**
 * File containing the abstract Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RelationList\RelationListStorage\Gateway;

use eZ\Publish\Core\FieldType\Relation\RelationStorage\Gateway\LegacyStorage as RelationLegacyStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Relation as APIRelationValue;

/**
 * RelationList Legacy Gateway, extends RelationGateway
 */
class LegacyStorage extends RelationLegacyStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\Url\UrlStorage\Gateway
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();
        $destinationContentId = 0;

        // insert relation to ezcontentobject_link, but bind by reference to $destinationContentId
        // to avoid having to create several insert queries.
        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( self::TABLE )
        )->set(
            $dbHandler->quoteColumn( "contentclassattribute_id" ),
            $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "from_contentobject_id" ),
            $q->bindValue( $versionInfo->contentInfo->id, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "from_contentobject_version" ),
            $q->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "op_code" ),
            $q->bindValue( 0, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "relation_type" ),
            $q->bindValue( APIRelationValue::FIELD, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "to_contentobject_id" ),
            $q->bindParam( $destinationContentId, null, \PDO::PARAM_INT )// Reference to $destinationContentId
        );

        $stmt = $q->prepare();
        foreach ( $field->value->data['destinationContentIds'] as $dataDestinationContentId )
        {
            if ( $dataDestinationContentId === null )
                throw new \RuntimeException( "\$destinationContentId can not be of value null" );

            $destinationContentId = $dataDestinationContentId;
            $stmt->execute();
        }

        return false;
    }
}
