<?php
/**
 * File containing the EzcDatabase class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\LegacyStorage\Content\Type\ContentTypeGateway;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition;

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
     * Inserts a new conten type.
     *
     * @param Type $createStruct
     * @return mixed Type ID
     */
    public function insertType( Type $type )
    {
        throw new \RuntimeException( "Not implemented, yet" );
    }

    /**
     * Insert assignement of $typeId to $groupId.
     *
     * @param mixed $typeId
     * @param mixed $groupId
     * @return void
     */
    public function insertGroupAssignement( $typeId, $groupId )
    {
        throw new \RuntimeException( "Not implemented, yet" );
    }

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param FieldDefinition $fieldDefinition
     * @return mixed Field definition ID
     */
    public function insertFieldDefinition( $typeId, FieldDefinition $fieldDefinition )
    {
        throw new \RuntimeException( "Not implemented, yet" );
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
