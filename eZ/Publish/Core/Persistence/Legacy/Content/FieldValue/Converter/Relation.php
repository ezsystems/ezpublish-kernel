<?php
/**
 * File containing the Relation converter
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use PDO;

class Relation implements Converter
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $db;

    /**
     * Create instance of RelationList converter
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $db
     */
    public function __construct( DatabaseHandler $db )
    {
        $this->db = $db;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataInt = $this->isValidDestinationContentId( $value->data['destinationContentId'] )
            ? $value->data['destinationContentId']
            : null;
        $storageFieldValue->sortKeyInt = (int)$value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $destinationContentId = $this->isValidDestinationContentId( $value->dataInt ) ? $value->dataInt : null;

        $fieldValue->data = array(
            "destinationContentId" => $destinationContentId ?: null,
        );
        $fieldValue->sortKey = (int)$value->sortKeyInt;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        // Selection method, 0 = browse, 1 = dropdown
        $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->fieldSettings['selectionMethod'];

        // Selection root, location ID
        $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->fieldSettings['selectionRoot'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        // Selection method, 0 = browse, 1 = dropdown
        $fieldDef->fieldTypeConstraints->fieldSettings['selectionMethod'] = $storageDef->dataInt1;

        // Selection root, location ID

        $fieldDef->fieldTypeConstraints->fieldSettings['selectionRoot'] =
            $storageDef->dataInt2 === 0
            ? ''
            : $storageDef->dataInt2;
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return false
     */
    public function getIndexColumn()
    {
        return 'sort_key_int';
    }

    /**
     * Checks wether $contentId relates to an existing content
     *
     * @param $destinationContentId
     * @return bool
     */
    private function isValidDestinationContentId( $destinationContentId )
    {
        if ( !$destinationContentId )
        {
            return false;
        }

        $q = $this->db->createSelectQuery();
        $q
            ->select( $q->expr->count( '*' ) )
            ->from( $this->db->quoteTable( 'ezcontentobject' ) )
            ->leftJoin(
                $this->db->quoteTable( 'ezcontentobject_tree' ),
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->db->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' ),
                        $this->db->quoteColumn( 'id', 'ezcontentobject' )
                    ),
                    $q->expr->eq(
                        $this->db->quoteColumn( 'node_id', 'ezcontentobject_tree' ),
                        $this->db->quoteColumn( 'main_node_id', 'ezcontentobject_tree' )
                    )
                )
            )
            ->leftJoin(
                $this->db->quoteTable( 'ezcontentclass' ),
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->db->quoteColumn( 'id', 'ezcontentclass' ),
                        $this->db->quoteColumn( 'contentclass_id', 'ezcontentobject' )
                    ),
                    $q->expr->eq(
                        $this->db->quoteColumn( 'version', 'ezcontentclass' ),
                        $q->bindValue( ContentType::STATUS_DEFINED, null, PDO::PARAM_INT )
                    )
                )
            )
            ->where(
                $q->expr->eq(
                    $this->db->quoteColumn( 'id', 'ezcontentobject' ),
                    $destinationContentId
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}
