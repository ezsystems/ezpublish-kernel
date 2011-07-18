<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\ContentTypeUpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group;

/**
 * Mapper for ContentTypeHandler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     * @return array(Type)
     */
    public function extractTypesFromRows( array $rows )
    {
        throw new \RuntimeException( "Not implemented, yet" );
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param Type $type
     * @param ContentTypeCreateStruct $struct
     * @return void
     */
    public function createTypeFromCreateStruct( ContentTypeCreateStruct $createStruct )
    {
        $type = new Type();

        $type->name                = $createStruct->name;
        $type->description         = $createStruct->description;
        $type->identifier          = $createStruct->identifier;
        $type->created             = $createStruct->created;
        $type->modified            = $createStruct->modified;
        $type->creatorId           = $createStruct->creatorId;
        $type->modifierId          = $createStruct->modifierId;
        $type->remoteId            = $createStruct->remoteId;
        $type->urlAliasSchema      = $createStruct->urlAliasSchema;
        $type->nameSchema          = $createStruct->nameSchema;
        $type->isContainer         = $createStruct->isContainer;
        $type->initialLanguageId   = $createStruct->initialLanguageId;
        $type->contentTypeGroupIds = $createStruct->contentTypeGroupIds;
        $type->fieldDefinitions    = $createStruct->fieldDefinitions;

        return $type;
    }
}
