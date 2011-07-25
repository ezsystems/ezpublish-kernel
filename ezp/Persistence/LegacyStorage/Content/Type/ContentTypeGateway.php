<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\GroupUpdateStruct;

/**
 * Base class for content type gateways.
 */
abstract class ContentTypeGateway
{
    /**
     * Inserts the given $group.
     *
     * @return mixed Group ID
     */
    abstract public function insertGroup( Group $group );

    /**
     * Updates a group with data in $group.
     *
     * @param GroupUpdateStruct $group
     * @return void
     */
    abstract public function updateGroup( GroupUpdateStruct $group );

    /**
     * Inserts a new conten type.
     *
     * @param Type $createStruct
     * @return mixed Type ID
     */
    abstract public function insertType( Type $type );

    /**
     * Insert assignement of $typeId to $groupId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param mixed $groupId
     * @return void
     */
    abstract public function insertGroupAssignement( $typeId, $version, $groupId );

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $version
     * @param FieldDefinition $fieldDefinition
     * @return mixed Field definition ID
     */
    abstract public function insertFieldDefinition( $typeId, $version, FieldDefinition $fieldDefinition );

    /**
     * Loads an array with data about $typeId in $version.
     *
     * @param mixed $typeId
     * @param int $version
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    abstract public function loadTypeData( $typeId, $version );

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteFieldDefinitionsForType( $typeId, $version );

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteType( $typeId, $version );

    /**
     * Deletes all group assignements for a Type.
     *
     * @param mixed $typeId
     * @return void
     */
    abstract public function deleteGroupAssignementsForType( $typeId, $version );
}
