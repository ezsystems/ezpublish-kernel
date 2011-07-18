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
        $this->db = $db;
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
        throw new \RuntimeException( "Not implemented, yet" );
    }
}
