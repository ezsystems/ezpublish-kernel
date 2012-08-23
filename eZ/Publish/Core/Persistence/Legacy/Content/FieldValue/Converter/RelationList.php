<?php
/**
 * File containing the Relation converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\FieldType\RelationList\Value as RelationListValue,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    DOMDocument;

class RelationList implements Converter
{
    /**
     * @var \ezcDbHandler
     */
    private $db;

    /**
     * Create instance of RelationList converter
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $db
     */
    public function __construct( EzcDbHandler $db )
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
        $storageFieldValue->dataText = $this->generateXmlString( $value );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $this->restoreValueFromXmlString( $value->dataText, $fieldValue );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     *
     * @todo Implement, legacy format is xml, see RelationList\Type & eZObjectRelationListType for more info
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * <?xml version="1.0" encoding="utf-8"?>
     * <related-objects>
     *   <constraints>
     *     <allowed-class contentclass-identifier="blog_post"/>
     *   </constraints>
     *   <type value="2"/>
     *   <selection_type value="1"/>
     *   <object_class value=""/>
     *   <contentobject-placement node-id="67"/>
     * </related-objects>
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     *
     * @todo Implement, legacy format is xml, {@see toStorageFieldDefinition()}
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return bool
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }

    /**
     * Generates XML string from $authorValue to be stored in storage engine
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     *
     * @throws \RuntimeException
     * @return string The generated XML string
     */
    private function generateXmlString( FieldValue $value )
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElement( 'related-objects' );
        $doc->appendChild( $root );

        $relationList = $doc->createElement( 'relation-list' );
        foreach ( $this->getRelationXmlHashFromDB( $value->data['destinationContentIds'] ) as $row )
        {
            $relationItem = $doc->createElement( 'relation-item' );
            foreach( self::dbAttributeMap() as $domAttrKey => $propertyKey )
            {
                if ( !isset( $row[$propertyKey] ) )
                    throw new \RuntimeException( "Missing relation-item external data property: $propertyKey" );

                $relationItem->setAttribute( $domAttrKey, $row[$propertyKey] );
            }
            $relationList->appendChild( $relationItem );
            unset( $relationItem );
        }

        $root->appendChild( $relationList );
        $doc->appendChild( $root );

        return $doc->saveXML();
    }

    /**
     * Restores an author Value object from $xmlString
     *
     * @param string $xmlString XML String stored in storage engine
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return void
     */
    private function restoreValueFromXmlString( $xmlString, FieldValue $fieldValue )
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $fieldValue->data = array( 'destinationContentIds' => array() );
        if ( $xmlString !== null && $dom->loadXML( $xmlString ) === true )
        {
            foreach ( $dom->getElementsByTagName( 'relation-item' ) as $relationItem )
            {
                /** @var \DOMElement $relationItem */
                $fieldValue->data['destinationContentIds'][] = $relationItem->getAttribute( 'contentobject-id' );;
            }
        }
    }

    /**
     * @param mixed[] $destinationContentIds
     *
     * @throws \Exception
     * @return array
     */
    private function getRelationXmlHashFromDB( array $destinationContentIds )
    {
        if ( empty( $destinationContentIds ) )
            return array();

        $q = $this->db->createSelectQuery();
        $q
            ->select(
                $this->db->aliasedColumn( $q, 'id', 'ezcontentobject' ),
                $this->db->aliasedColumn( $q, 'remote_id', 'ezcontentobject' ),
                $this->db->aliasedColumn( $q, 'current_version', 'ezcontentobject' ),
                $this->db->aliasedColumn( $q, 'contentclass_id', 'ezcontentobject' ),
                $this->db->aliasedColumn( $q, 'node_id', 'ezcontentobject_tree' ),
                $this->db->aliasedColumn( $q, 'parent_node_id', 'ezcontentobject_tree' )
            )
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
            ->where(
                $q->expr->in(
                    $this->db->quoteColumn( 'id' ),
                    $destinationContentIds
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );

        if ( empty( $rows ) )
            throw new \Exception( "Could find Content with id's" . var_export( $destinationContentIds, true ) );
        else if ( count( $rows ) !== count( $destinationContentIds ) )
            throw new \Exception( "Miss match of rows & id count:" . var_export( $destinationContentIds, true ) );

        // Add priority starting from 1
        for ( $i = 0; isset( $rows[$i] ); ++$i )
        {
            $rows[$i]['priority'] = $i+1;
        }

        return $rows;
    }

    /**
     * @static
     * @return array
     */
    static private function dbAttributeMap()
    {
        return array(
            // 'identifier' => 'identifier',// not used
            'priority' => 'priority',
            // 'in-trash' => 'in_trash',// false by default and implies
            'contentobject-id' => 'ezcontentobject_id',
            'contentobject-version' => 'ezcontentobject_current_version',
            'node-id' => 'ezcontentobject_tree_node_id',
            'parent-node-id' => 'ezcontentobject_tree_parent_node_id',
            'contentclass-id' => 'ezcontentobject_contentclass_id',
            //'contentclass-identifier' => 'contentclass_identifier',@todo Re add
            // 'is-modified' => 'is_modified',// deprecated and not used
            'contentobject-remote-id' => 'ezcontentobject_remote_id'
        );
    }
}
