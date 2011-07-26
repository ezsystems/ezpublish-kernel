<?php
/**
 * File containing the EzcDatabase class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway,
    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\ContentTypeUpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\GroupUpdateStruct;

/**
 * Zeta Component Database based content type gateway.
 */
class EzcDatabase extends ContentTypeGateway
{
    /**
     * Columns of database tables.
     *
     * @var array
     */
    protected $columns = array(
        'ezcontentclass' => array(
            'id',
            'always_available',
            'contentobject_name',
            'created',
            'creator_id',
            'modified',
            'modifier_id',
            'identifier',
            'initial_language_id',
            'is_container',
            'language_mask',
            'remote_id',
            'serialized_description_list',
            'serialized_name_list',
            'sort_field',
            'sort_order',
            'url_alias_name',
            'version',
        ),
        'ezcontentclass_attribute' => array(
            'id',
            'can_translate',
            'category',
            'contentclass_id',
            'data_float1',
            'data_float2',
            'data_float3',
            'data_float4',
            'data_int1',
            'data_int2',
            'data_int3',
            'data_int4',
            'data_text1',
            'data_text2',
            'data_text3',
            'data_text4',
            'data_text5',
            'data_type_string',
            'identifier',
            'is_information_collector',
            'is_required',
            'is_searchable',
            'placement',
            'serialized_data_text',
            'serialized_description_list',
            'serialized_name_list',
        ),
    );

    /**
     * Zeta Components database handler.
     *
     * @var ezcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new gateway based on $db
     *
     * @param ezcDbHandler $db
     */
    public function __construct( \ezcDbHandler $db )
    {
        $this->dbHandler = $db;
    }

    /**
     * Inserts the given $group.
     *
     * @return mixed Group ID
     * @todo This might lead to race conditions for insert IDs.
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     * @todo Isn't $identifier more the "name"?
     */
    public function insertGroup( Group $group )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteIdentifier( 'ezcontentclassgroup' )
        )->set(
            $this->dbHandler->quoteIdentifier( 'created' ),
            $q->bindValue( $group->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'creator_id' ),
            $q->bindValue( $group->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modified' ),
            $q->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modifier_id' ),
            $q->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'name' ),
            $q->bindValue( $group->name[$group->name['always-available']] )
        );
        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Updates a group with data in $group.
     *
     * @param GroupUpdateStruct $group
     * @return void
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteIdentifier( 'ezcontentclassgroup' )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modified' ),
            $q->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modifier_id' ),
            $q->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'name' ),
            $q->bindValue( $group->name[$group->name['always-available']] )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Inserts a new conten type.
     *
     * @param Type $createStruct
     * @return mixed Type ID
     * @todo This might lead to race conditions for insert IDs.
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     */
    public function insertType( Type $type )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto( 'ezcontentclass' );
        $q->set(
            $this->dbHandler->quoteIdentifier( 'version' ),
            $q->bindValue( $type->version, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'created' ),
            $q->bindValue( $type->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'creator_id' ),
            $q->bindValue( $type->creatorId, null, \PDO::PARAM_INT )
        );
        $this->setCommonTypeColumns( $q, $type );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Set common columns for insert/update of a Type.
     *
     * @param \ezcQuerySelect $q
     * @param mixed $typeStruct
     * @return void
     */
    protected function setCommonTypeColumns( \ezcQuery $q, $type )
    {
        $q->set(
            $this->dbHandler->quoteIdentifier( 'serialized_name_list' ),
            $q->bindValue( serialize( $type->name ) )
        )->set(
            $this->dbHandler->quoteIdentifier( 'serialized_description_list' ),
            $q->bindValue( serialize( $type->description ) )
        )->set(
            $this->dbHandler->quoteIdentifier( 'identifier' ),
            $q->bindValue( $type->identifier )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modified' ),
            $q->bindValue( $type->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'modifier_id' ),
            $q->bindValue( $type->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'remote_id' ),
            $q->bindValue( $type->remoteId )
        )->set(
            $this->dbHandler->quoteIdentifier( 'url_alias_name' ),
            $q->bindValue( $type->urlAliasSchema )
        )->set(
            $this->dbHandler->quoteIdentifier( 'contentobject_name' ),
            $q->bindValue( $type->nameSchema )
        )->set(
            $this->dbHandler->quoteIdentifier( 'is_container' ),
            $q->bindValue( $type->isContainer ? 1 : 0, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'initial_language_id' ),
            $q->bindValue( $type->initialLanguageId, null, \PDO::PARAM_INT )
        );
    }

    /**
     * Insert assignement of $typeId to $groupId.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $version
     * @return void
     */
    public function insertGroupAssignement( $groupId, $typeId, $version )
    {
        $group = $this->loadGroupData( $groupId );

        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto( 'ezcontentclass_classgroup' )
            ->set(
                $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                $q->bindValue( $typeId, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteIdentifier( 'contentclass_version' ),
                $q->bindValue( $version, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteIdentifier( 'group_id' ),
                $q->bindValue( $groupId, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteIdentifier( 'group_name' ),
                $q->bindValue( $group['name'] )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Deletes a group assignements for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $version
     * @return void
     */
    public function deleteGroupAssignement( $groupId, $typeId, $version )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom( 'ezcontentclass_classgroup' )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                        $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'contentclass_version' ),
                        $q->bindValue( $version, null, \PDO::PARAM_INT )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'group_id' ),
                        $q->bindValue( $groupId, null, \PDO::PARAM_INT )
                    )
                )
        );
        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Loads data about Group with $groupId.
     *
     * @param mixed $groupId
     * @return string[]
     */
    protected function loadGroupData( $groupId )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $this->dbHandler->quoteIdentifier( 'created' ),
            $this->dbHandler->quoteIdentifier( 'creator_id' ),
            $this->dbHandler->quoteIdentifier( 'id' ),
            $this->dbHandler->quoteIdentifier( 'modified' ),
            $this->dbHandler->quoteIdentifier( 'modifier_id' ),
            $this->dbHandler->quoteIdentifier( 'name' )
        )->from(
            $this->dbHandler->quoteIdentifier( 'ezcontentclassgroup' )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteIdentifier( 'id' ),
                $q->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param FieldDefinition $fieldDefinition
     * @return mixed Field definition ID
     * @todo What about fieldTypeConstraints and defaultValue?
     * @todo This might lead to race conditions for insert IDs.
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     */
    public function insertFieldDefinition( $typeId, $version, FieldDefinition $fieldDefinition )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto( 'ezcontentclass_attribute' );
        $q->set(
            $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
            $q->bindValue( $typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'version' ),
            $q->bindValue( $version, null, \PDO::PARAM_INT )
        );
        $this->setCommonFieldColumns( $q, $fieldDefinition );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Set common columns for insert/update of FieldDefinition.
     *
     * @param \ezcQuery $q
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    protected function setCommonFieldColumns( \ezcQuery $q, FieldDefinition $fieldDefinition )
    {
        $q->set(
            $this->dbHandler->quoteIdentifier( 'serialized_name_list' ),
            $q->bindValue( serialize( $fieldDefinition->name ) )
        )->set(
            $this->dbHandler->quoteIdentifier( 'serialized_description_list' ),
            $q->bindValue( serialize( $fieldDefinition->description ) )
        )->set(
            $this->dbHandler->quoteIdentifier( 'identifier' ),
            $q->bindValue( $fieldDefinition->identifier )
        )->set(
            $this->dbHandler->quoteIdentifier( 'category' ),
            $q->bindValue( $fieldDefinition->fieldGroup )
        )->set(
            $this->dbHandler->quoteIdentifier( 'placement' ),
            $q->bindValue( $fieldDefinition->position, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'data_type_string' ),
            $q->bindValue( $fieldDefinition->fieldType )
        )->set(
            $this->dbHandler->quoteIdentifier( 'can_translate' ),
            $q->bindValue( ( $fieldDefinition->isTranslatable ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteIdentifier( 'is_information_collector' ),
            $q->bindValue( ( $fieldDefinition->isInfoCollector ? 1 : 0 ), null, \PDO::PARAM_INT )
        /*
         * fieldTypeConstraints?
        )->set(
            $this->dbHandler->quoteIdentifier( '' ),
            $q->bindValue( $fieldDefinition-> )
        */
        )->set(
            // @todo: Correct?
            $this->dbHandler->quoteIdentifier( 'serialized_data_text' ),
            $q->bindValue( serialize( $fieldDefinition->defaultValue ) )
        );
    }

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $version
     * @param mixed $fieldDefinitionId
     * @return void
     */
    public function deleteFieldDefinition( $typeId, $version, $fieldDefinitionId )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteIdentifier( 'ezcontentclass_attribute' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'id' ),
                    $q->bindValue( $fieldDefinitionId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'version' ),
                    $q->bindValue( $version, null, \PDO::PARAM_INT )
                ),
                // FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $typeId, $version, FieldDefinition $fieldDefinition )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update( 'ezcontentclass_attribute' )
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'id' ),
                    $q->bindValue( $fieldDefinition->id, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'version' ),
                    $q->bindValue( $version, null, \PDO::PARAM_INT )
                ),
                // FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                )
            );
        $this->setCommonFieldColumns( $q, $fieldDefinition );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $typeId
     * @param int $version
     * @param ContentTypeUpdateStruct $updateStruct
     * @return void
     */
    public function updateType( $typeId, $version, ContentTypeUpdateStruct $updateStruct )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update( $this->dbHandler->quoteIdentifier( 'ezcontentclass' ) );

        $this->setCommonTypeColumns( $q, $updateStruct );

        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteIdentifier( 'version' ),
                    $q->bindValue( $version, null, \PDO::PARAM_INT )
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Loads an array with data about $typeId in $version.
     *
     * @param mixed $typeId
     * @param int $version
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeData( $typeId, $version )
    {
        $q = $this->dbHandler->createSelectQuery();

        $this->selectColumns( $q, 'ezcontentclass' );
        $this->selectColumns( $q, 'ezcontentclass_attribute' );
        $q->select(
            $this->createTableColumnAlias(
                $q,
                'ezcontentclass_classgroup',
                'group_id'
            )
        );
        $q->from(
            $this->dbHandler->quoteIdentifier( 'ezcontentclass' )
        )->leftJoin(
            $this->dbHandler->quoteIdentifier( 'ezcontentclass_attribute' ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->qualifiedIdentifier(
                        'ezcontentclass',
                        'id'
                    ),
                    $this->qualifiedIdentifier(
                        'ezcontentclass_attribute',
                        'contentclass_id'
                    )
                ),
                $q->expr->eq(
                    $this->qualifiedIdentifier(
                        'ezcontentclass',
                        'version'
                    ),
                    $this->qualifiedIdentifier(
                        'ezcontentclass_attribute',
                        'version'
                    )
                )
            )
        )->leftJoin(
            $this->dbHandler->quoteIdentifier( 'ezcontentclass_classgroup' ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->qualifiedIdentifier(
                        'ezcontentclass',
                        'id'
                    ),
                    $this->qualifiedIdentifier(
                        'ezcontentclass_classgroup',
                        'contentclass_id'
                    )
                ),
                $q->expr->eq(
                    $this->qualifiedIdentifier(
                        'ezcontentclass',
                        'version'
                    ),
                    $this->qualifiedIdentifier(
                        'ezcontentclass_classgroup',
                        'contentclass_version'
                    )
                )
            )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->qualifiedIdentifier( 'ezcontentclass', 'id' ),
                    $q->bindValue( $typeId )
                ),
                $q->expr->eq(
                    $this->qualifiedIdentifier( 'ezcontentclass', 'version' ),
                    $q->bindValue( $version )
                )
            )
        );
        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    public function deleteFieldDefinitionsForType( $typeId, $version )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom( 'ezcontentclass_attribute' )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                        $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'version' ),
                        $q->bindValue( $version, null, \PDO::PARAM_INT )
                    )
                )
        );
        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @return void
     */
    public function deleteType( $typeId, $version )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom( 'ezcontentclass' )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'id' ),
                        $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'version' ),
                        $q->bindValue( $version, null, \PDO::PARAM_INT )
                    )
                )
        );
        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Deletes all group assignements for a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    public function deleteGroupAssignementsForType( $typeId, $version )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom( 'ezcontentclass_classgroup' )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'contentclass_id' ),
                        $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteIdentifier( 'contentclass_version' ),
                        $q->bindValue( $version, null, \PDO::PARAM_INT )
                    )
                )
        );
        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Creates an array of select columns for $tableName.
     *
     * @param string $tableName
     * @return array
     */
    protected function selectColumns( \ezcQuerySelect $q, $tableName )
    {
        foreach ( $this->columns[$tableName] as $col )
        {
            $q->select(
                $this->createTableColumnAlias( $q, $tableName, $col )
            );
        }
    }

    /**
     * Creates an alias for $tableName, $columnName in $q.
     *
     * @param ezcDbQuery $q
     * @param string $tableName
     * @param string $columnName
     * @return string
     */
    protected function createTableColumnAlias( \ezcQuerySelect $q, $tableName, $columnName )
    {
        return $q->alias(
            $this->qualifiedIdentifier( $tableName, $columnName ),
            $this->dbHandler->quoteIdentifier(
                sprintf(
                    '%s_%s',
                    $tableName,
                    $columnName
                )
            )
        );
    }

    /**
     * Returns a qualified identifier for $column in $table.
     *
     * @param string $table
     * @param string $column
     * @return string
     */
    protected function qualifiedIdentifier( $table, $column )
    {
        return sprintf(
            '%s.%s',
            $this->dbHandler->quoteIdentifier( $table ),
            $this->dbHandler->quoteIdentifier( $column )
        );
    }
}
