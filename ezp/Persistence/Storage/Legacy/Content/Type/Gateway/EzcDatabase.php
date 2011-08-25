<?php
/**
 * File containing the EzcDatabase class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Type\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;

/**
 * Zeta Component Database based content type gateway.
 */
class EzcDatabase extends Gateway
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
     * @var EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Cache for language mapping information
     *
     * @var array
     */
    protected $languageMapping;

    /**
     * Creates a new gateway based on $db
     *
     * @param EzcDbHandler $db
     */
    public function __construct( EzcDbHandler $db )
    {
        $this->dbHandler = $db;
    }

    /**
     * Get language mapping
     *
     * Get mapping of languages to their respective IDs in the database.
     *
     * @return array
     */
    protected function getLanguageMapping()
    {
        if ( $this->languageMapping )
        {
            return $this->languageMapping;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'id' ),
                $this->dbHandler->quoteColumn( 'locale' )
            )->from(
                $this->dbHandler->quoteTable( 'ezcontent_language' )
            );

        $statement = $query->prepare();
        $statement->execute();

        $this->languageMapping = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $this->languageMapping[$row['locale']] = (int) $row['id'];
        }

        return $this->languageMapping;
    }

    /**
     * Get language mask
     *
     * Return the language mask for a common array of language specifications
     * for a type name or description.
     *
     * @param array $languages
     * @return int
     */
    protected function getLanguageMask( array $languages )
    {
        $mask    = 0;
        if ( isset( $languages['always-available'] ) )
        {
            $mask |= $languages['always-available'] ? 1 : 0;
            unset( $languages['always-available'] );
        }

        $mapping = $this->getLanguageMapping();
        foreach ( $languages as $language => $value )
        {
            $mask |= $mapping[$language];
        }

        return $mask;
    }

    /**
     * Inserts the given $group.
     *
     * @return mixed Group ID
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     * @todo Isn't $identifier more the "name"?
     */
    public function insertGroup( Group $group )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentclassgroup' )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $q->bindValue( $group->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $q->bindValue( $group->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $q->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $group->name[$group->name['always-available']] )
        );
        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Updates a group with data in $group.
     *
     * @param \ezp\Persistence\Content\Type\Group\UpdateStruct $group
     * @return void
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteColumn( 'ezcontentclassgroup' )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $group->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $q->bindValue( $group->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $group->name[$group->name['always-available']] )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Returns the number of types in a certain group.
     *
     * @param int $groupId
     * @return int
     */
    public function countTypesInGroup( $groupId )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $q->alias(
                $q->expr->count(
                    $this->dbHandler->quoteColumn( 'contentclass_id' )
                ),
                'count'
            )
        )->from( $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' ) )
        ->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'group_id' ),
                $q->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     * @return int
     */
    public function countGroupsForType( $typeId, $status )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $q->alias(
                $q->expr->count(
                    $this->dbHandler->quoteColumn( 'group_id' )
                ),
                'count'
            )
        )->from( $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' ) )
        ->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                )
            ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     * @return void
     */
    public function deleteGroup( $groupId )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom( $this->dbHandler->quoteTable( 'ezcontentclassgroup' ) )
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $groupId, null, \PDO::PARAM_INT )
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Inserts data into contentclass_name.
     *
     * @param Type $type
     * @return void
     */
    protected function insertTypeNameData( Type $type )
    {
        $alwaysAvailable = null;
        $languages = $type->name;
        $mapping = $this->getLanguageMapping();
        if ( isset( $languages['always-available'] ) )
        {
            $alwaysAvailable = $languages['always-available'];
            unset( $languages['always-available'] );
        }

        foreach ( $languages as $language => $name )
        {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto( $this->dbHandler->quoteTable( 'ezcontentclass_name' ) )
                ->set( 'contentclass_id', $query->bindValue( $type->id ) )
                ->set( 'contentclass_version', $query->bindValue( $type->status ) )
                ->set( 'language_id', $query->bindValue( $mapping[$language] | ( $alwaysAvailable === $language ? 1 : 0 ) ) )
                ->set( 'language_locale', $query->bindValue( $language ) )
                ->set( 'name', $query->bindValue( $name ) );
            $query->prepare()->execute();
        }
    }

    /**
     * Inserts a new conten type.
     *
     * @param Type $createStruct
     * @return mixed Type ID
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     */
    public function insertType( Type $type )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto( $this->dbHandler->quoteTable( 'ezcontentclass' ) );
        $q->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $type->status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $q->bindValue( $type->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $q->bindValue( $type->creatorId, null, \PDO::PARAM_INT )
        );
        $this->setCommonTypeColumns( $q, $type );
        $q->prepare()->execute();

        $type->id = $this->dbHandler->lastInsertId();
        $this->insertTypeNameData( $type );

        return $type->id;
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
            $this->dbHandler->quoteColumn( 'serialized_name_list' ),
            $q->bindValue( serialize( $type->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'serialized_description_list' ),
            $q->bindValue( serialize( $type->description ) )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $q->bindValue( $type->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $type->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $q->bindValue( $type->modifierId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'remote_id' ),
            $q->bindValue( $type->remoteId )
        )->set(
            $this->dbHandler->quoteColumn( 'url_alias_name' ),
            $q->bindValue( $type->urlAliasSchema )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_name' ),
            $q->bindValue( $type->nameSchema )
        )->set(
            $this->dbHandler->quoteColumn( 'is_container' ),
            $q->bindValue( $type->isContainer ? 1 : 0, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $q->bindValue( $this->getLanguageMask( $type->name ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $type->initialLanguageId, null, \PDO::PARAM_INT )
        );
    }

    /**
     * Insert assignement of $typeId to $groupId.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     * @return void
     */
    public function insertGroupAssignement( $groupId, $typeId, $status )
    {
        $groups = $this->loadGroupData( $groupId );
        $group = $groups[0];

        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclass_id' ),
            $q->bindValue( $typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclass_version' ),
            $q->bindValue( $status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'group_id' ),
            $q->bindValue( $groupId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'group_name' ),
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
     * @param int $status
     * @return void
     */
    public function deleteGroupAssignement( $groupId, $typeId, $status )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'group_id' ),
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
     * @return string[][]
     */
    public function loadGroupData( $groupId )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            $this->dbHandler->quoteColumn( 'created' ),
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->quoteColumn( 'modified' ),
            $this->dbHandler->quoteColumn( 'modifier_id' ),
            $this->dbHandler->quoteColumn( 'name' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentclassgroup' )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Returns an array with data about all Group objects.
     *
     * @return array
     */
    public function loadAllGroupsData()
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param FieldDefinition $fieldDefinition
     * @return mixed Field definition ID
     * @todo What about fieldTypeConstraints and defaultValue?
     * @todo PDO->lastInsertId() might require a seq name (Oracle?).
     */
    public function insertFieldDefinition( $typeId, $status, FieldDefinition $fieldDefinition )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto( $this->dbHandler->quoteTable( 'ezcontentclass_attribute' ) );
        $q->set(
            $this->dbHandler->quoteColumn( 'contentclass_id' ),
            $q->bindValue( $typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $status, null, \PDO::PARAM_INT )
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
            $this->dbHandler->quoteColumn( 'serialized_name_list' ),
            $q->bindValue( serialize( $fieldDefinition->name ) )
        )->set(
            $this->dbHandler->quoteColumn( 'serialized_description_list' ),
            $q->bindValue( serialize( $fieldDefinition->description ) )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $q->bindValue( $fieldDefinition->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'category' ),
            $q->bindValue( $fieldDefinition->fieldGroup )
        )->set(
            $this->dbHandler->quoteColumn( 'placement' ),
            $q->bindValue( $fieldDefinition->position, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'data_type_string' ),
            $q->bindValue( $fieldDefinition->fieldType )
        )->set(
            $this->dbHandler->quoteColumn( 'can_translate' ),
            $q->bindValue( ( $fieldDefinition->isTranslatable ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'is_required' ),
            $q->bindValue( ( $fieldDefinition->isRequired ? 1 : 0 ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'is_information_collector' ),
            $q->bindValue( ( $fieldDefinition->isInfoCollector ? 1 : 0 ), null, \PDO::PARAM_INT )
        /*
         * fieldTypeConstraints?
        )->set(
            $this->dbHandler->quoteIdentifier( '' ),
            $q->bindValue( $fieldDefinition-> )
        */
        )->set(
            // @todo: Correct?
            $this->dbHandler->quoteColumn( 'serialized_data_text' ),
            $q->bindValue( serialize( $fieldDefinition->defaultValue ) )
        );
    }

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $fieldDefinitionId
     * @return void
     */
    public function deleteFieldDefinition( $typeId, $status, $fieldDefinitionId )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentclass_attribute' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $fieldDefinitionId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
                ),
                // FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
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
     * @param int $status
     * @param FieldDefinition $fieldDefinition
     * @return void
     */
    public function updateFieldDefinition( $typeId, $status, FieldDefinition $fieldDefinition )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q
            ->update(
                $this->dbHandler->quoteTable( 'ezcontentclass_attribute' )
            )->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $fieldDefinition->id, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
                ),
                // FIXME: Actually not needed
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
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
     * @param int $status
     * @param \ezp\Persistence\Content\Type\UpdateStruct $updateStruct
     * @return void
     */
    public function updateType( $typeId, $status, UpdateStruct $updateStruct )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update( $this->dbHandler->quoteTable( 'ezcontentclass' ) );

        $this->setCommonTypeColumns( $q, $updateStruct );

        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
                )
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Loads an array with data about $typeId in $status.
     *
     * @param mixed $typeId
     * @param int $status
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    public function loadTypeData( $typeId, $status )
    {
        $q = $this->dbHandler->createSelectQuery();

        $this->selectColumns( $q, 'ezcontentclass' );
        $this->selectColumns( $q, 'ezcontentclass_attribute' );
        $q->select(
            $this->dbHandler->aliasedColumn(
                $q,
                'group_id',
                'ezcontentclass_classgroup'
            )
        );
        $q->from(
            $this->dbHandler->quoteTable( 'ezcontentclass' )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentclass_attribute' ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn(
                        'id',
                        'ezcontentclass'
                    ),
                    $this->dbHandler->quoteColumn(
                        'contentclass_id',
                        'ezcontentclass_attribute'
                    )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn(
                        'version',
                        'ezcontentclass'
                    ),
                    $this->dbHandler->quoteColumn(
                        'version',
                        'ezcontentclass_attribute'
                    )
                )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' ),
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn(
                        'id',
                        'ezcontentclass'
                    ),
                    $this->dbHandler->quoteColumn(
                        'contentclass_id',
                        'ezcontentclass_classgroup'
                    )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn(
                        'version',
                        'ezcontentclass'
                    ),
                    $this->dbHandler->quoteColumn(
                        'contentclass_version',
                        'ezcontentclass_classgroup'
                    )
                )
            )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentclass' ),
                    $q->bindValue( $typeId )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentclass' ),
                    $q->bindValue( $status )
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
    public function deleteFieldDefinitionsForType( $typeId, $status )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentclass_attribute' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
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
    public function deleteType( $typeId, $status )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentclass' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
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
    public function deleteGroupAssignementsForType( $typeId, $status )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentclass_classgroup' )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_id' ),
                    $q->bindValue( $typeId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclass_version' ),
                    $q->bindValue( $status, null, \PDO::PARAM_INT )
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
                $this->dbHandler->aliasedColumn( $q, $col, $tableName )
            );
        }
    }
}
