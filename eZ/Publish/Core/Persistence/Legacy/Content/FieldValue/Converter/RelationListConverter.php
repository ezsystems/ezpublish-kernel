<?php

/**
 * File containing the Relation converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type as ContentType;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use DOMDocument;
use PDO;

class RelationListConverter implements Converter
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $db;

    /**
     * Create instance of RelationList converter.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $db
     */
    public function __construct(DatabaseHandler $db)
    {
        $this->db = $db;
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement('related-objects');
        $doc->appendChild($root);

        $relationList = $doc->createElement('relation-list');
        $data = $this->getRelationXmlHashFromDB($value->data['destinationContentIds']);
        $priority = 0;

        foreach ($value->data['destinationContentIds'] as $id) {
            $row = $data[$id][0];
            $row['ezcontentobject_id'] = $id;
            $row['priority'] = ($priority += 1);

            $relationItem = $doc->createElement('relation-item');
            foreach (self::dbAttributeMap() as $domAttrKey => $propertyKey) {
                if (!isset($row[$propertyKey])) {
                    throw new \RuntimeException("Missing relation-item external data property: $propertyKey");
                }

                $relationItem->setAttribute($domAttrKey, $row[$propertyKey]);
            }
            $relationList->appendChild($relationItem);
            unset($relationItem);
        }

        $root->appendChild($relationList);
        $doc->appendChild($root);

        $storageFieldValue->dataText = $doc->saveXML();
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = array('destinationContentIds' => array());
        if ($value->dataText === null) {
            return;
        }

        $priorityByContentId = array();

        $dom = new DOMDocument('1.0', 'utf-8');
        if ($dom->loadXML($value->dataText) === true) {
            foreach ($dom->getElementsByTagName('relation-item') as $relationItem) {
                /* @var \DOMElement $relationItem */
                $priorityByContentId[$relationItem->getAttribute('contentobject-id')] =
                    $relationItem->getAttribute('priority');
            }
        }

        asort($priorityByContentId, SORT_NUMERIC);

        $fieldValue->data['destinationContentIds'] = array_keys($priorityByContentId);
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement('related-objects');
        $doc->appendChild($root);

        $constraints = $doc->createElement('constraints');
        if (!empty($fieldSettings['selectionContentTypes'])) {
            foreach ($fieldSettings['selectionContentTypes'] as $typeIdentifier) {
                $allowedClass = $doc->createElement('allowed-class');
                $allowedClass->setAttribute('contentclass-identifier', $typeIdentifier);
                $constraints->appendChild($allowedClass);
                unset($allowedClass);
            }
        }
        $root->appendChild($constraints);

        $type = $doc->createElement('type');
        $type->setAttribute('value', 2);//Deprecated advance object relation list type, set since 4.x does
        $root->appendChild($type);

        $objectClass = $doc->createElement('object_class');
        $objectClass->setAttribute('value', '');//Deprecated advance object relation class type, set since 4.x does
        $root->appendChild($objectClass);

        $selectionType = $doc->createElement('selection_type');
        if (isset($fieldSettings['selectionMethod'])) {
            $selectionType->setAttribute('value', (int)$fieldSettings['selectionMethod']);
        } else {
            $selectionType->setAttribute('value', 0);
        }
        $root->appendChild($selectionType);

        $defaultLocation = $doc->createElement('contentobject-placement');
        if (!empty($fieldSettings['selectionDefaultLocation'])) {
            $defaultLocation->setAttribute('node-id', (int)$fieldSettings['selectionDefaultLocation']);
        }
        $root->appendChild($defaultLocation);

        $doc->appendChild($root);
        $storageDef->dataText5 = $doc->saveXML();
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
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
     * <?xml version="1.0" encoding="utf-8"?>
     * <related-objects>
     *   <constraints/>
     *   <type value="2"/>
     *   <selection_type value="0"/>
     *   <object_class value=""/>
     *   <contentobject-placement/>
     * </related-objects>
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        // default settings
        $fieldDef->fieldTypeConstraints->fieldSettings = array(
            'selectionMethod' => 0,
            'selectionDefaultLocation' => null,
            'selectionContentTypes' => array(),
        );

        // default value
        $fieldDef->defaultValue = new FieldValue();
        $fieldDef->defaultValue->data = array('destinationContentIds' => array());

        if ($storageDef->dataText5 === null) {
            return;
        }

        // read settings from storage
        $fieldSettings = &$fieldDef->fieldTypeConstraints->fieldSettings;
        $dom = new DOMDocument('1.0', 'utf-8');
        if ($dom->loadXML($storageDef->dataText5) !== true) {
            return;
        }

        if ($selectionType = $dom->getElementsByTagName('selection_type')) {
            $fieldSettings['selectionMethod'] = (int)$selectionType->item(0)->getAttribute('value');
        }

        if (
            ($defaultLocation = $dom->getElementsByTagName('contentobject-placement')) &&
            $defaultLocation->item(0)->hasAttribute('node-id')
        ) {
            $fieldSettings['selectionDefaultLocation'] = (int)$defaultLocation->item(0)->getAttribute('node-id');
        }

        if (!($constraints = $dom->getElementsByTagName('constraints'))) {
            return;
        }

        foreach ($constraints->item(0)->getElementsByTagName('allowed-class') as $allowedClass) {
            $fieldSettings['selectionContentTypes'][] = $allowedClass->getAttribute('contentclass-identifier');
        }
    }

    /**
     * Returns the name of the index column in the attribute table.
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
     * @param mixed[] $destinationContentIds
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getRelationXmlHashFromDB(array $destinationContentIds)
    {
        if (empty($destinationContentIds)) {
            return array();
        }

        $q = $this->db->createSelectQuery();
        $q
            ->select(
                $this->db->aliasedColumn($q, 'id', 'ezcontentobject'),
                $this->db->aliasedColumn($q, 'remote_id', 'ezcontentobject'),
                $this->db->aliasedColumn($q, 'current_version', 'ezcontentobject'),
                $this->db->aliasedColumn($q, 'contentclass_id', 'ezcontentobject'),
                $this->db->aliasedColumn($q, 'node_id', 'ezcontentobject_tree'),
                $this->db->aliasedColumn($q, 'parent_node_id', 'ezcontentobject_tree'),
                $this->db->aliasedColumn($q, 'identifier', 'ezcontentclass')
            )
            ->from($this->db->quoteTable('ezcontentobject'))
            ->leftJoin(
                $this->db->quoteTable('ezcontentobject_tree'),
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->db->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                        $this->db->quoteColumn('id', 'ezcontentobject')
                    ),
                    $q->expr->eq(
                        $this->db->quoteColumn('node_id', 'ezcontentobject_tree'),
                        $this->db->quoteColumn('main_node_id', 'ezcontentobject_tree')
                    )
                )
            )
            ->leftJoin(
                $this->db->quoteTable('ezcontentclass'),
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->db->quoteColumn('id', 'ezcontentclass'),
                        $this->db->quoteColumn('contentclass_id', 'ezcontentobject')
                    ),
                    $q->expr->eq(
                        $this->db->quoteColumn('version', 'ezcontentclass'),
                        $q->bindValue(ContentType::STATUS_DEFINED, null, PDO::PARAM_INT)
                    )
                )
            )
            ->where(
                $q->expr->in(
                    $this->db->quoteColumn('id', 'ezcontentobject'),
                    $destinationContentIds
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        if (empty($rows)) {
            throw new \Exception("Could find Content with id's" . var_export($destinationContentIds, true));
        } elseif (count($rows) !== count($destinationContentIds)) {
            throw new \Exception('Miss match of rows & id count:' . var_export($destinationContentIds, true));
        }

        return $rows;
    }

    /**
     * @return array
     */
    private static function dbAttributeMap()
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
            'contentclass-identifier' => 'ezcontentclass_identifier',
            // 'is-modified' => 'is_modified',// deprecated and not used
            'contentobject-remote-id' => 'ezcontentobject_remote_id',
        );
    }
}
