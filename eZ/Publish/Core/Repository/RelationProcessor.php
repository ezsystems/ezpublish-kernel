<?php
/**
 * File containing the RelationProcessor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as SPIRelationCreateStruct;

/**
 * RelationProcessor is an internal service used for handling field relations upon Content creation or update.
 *
 * @internal
 */
class RelationProcessor
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct( RepositoryInterface $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
    }

    /**
     * Returns field relations data for the current version of the given $contentInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return mixed
     */
    public function getFieldRelations( ContentInfo $contentInfo )
    {
        $relations = array();
        $locationIdToContentIdMapping = array();
        $content = $this->repository->getContentService()->loadContentByContentInfo( $contentInfo );

        foreach ( $content->getFields() as $field )
        {
            $fieldDefinition = $content->contentType->getFieldDefinition( $field->fieldDefIdentifier );
            $fieldType = $this->repository->getFieldTypeService()->buildFieldType( $fieldDefinition->fieldTypeIdentifier );
            $this->appendFieldRelations(
                $relations,
                $locationIdToContentIdMapping,
                $fieldType,
                $fieldType->acceptValue( $field->value ),
                $fieldDefinition->id
            );
        }

        return $relations;
    }

    /**
     * Appends destination Content ids of given $fieldValue to the $relation array.
     *
     * If $fieldValue contains Location ids, the will be converted to the Content id that Location encapsulates.
     *
     * @param array $relations
     * @param array $locationIdToContentIdMapping An array with Location Ids as keys and corresponding Content Id as values
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue Accepted field value.
     * @param string $fieldDefinitionId
     *
     * @return void
     */
    public function appendFieldRelations(
        array &$relations,
        array &$locationIdToContentIdMapping,
        SPIFieldType $fieldType,
        BaseValue $fieldValue,
        $fieldDefinitionId
    )
    {
        foreach ( $fieldType->getRelations( $fieldValue ) as $relationType => $destinationIds )
        {
            if ( $relationType === Relation::FIELD )
            {
                if ( !isset( $relations[$relationType][$fieldDefinitionId] ) )
                {
                    $relations[$relationType][$fieldDefinitionId] = array();
                }
                $relations[$relationType][$fieldDefinitionId] += array_flip( $destinationIds );
            }
            // Using bitwise operators as Legacy Stack stores COMMON, LINK and EMBED relation types
            // in the same entry using bitmask
            else if ( $relationType & ( Relation::LINK | Relation::EMBED ) )
            {
                if ( !isset( $relations[$relationType] ) )
                {
                    $relations[$relationType] = array();
                }

                if ( isset( $destinationIds["locationIds"] ) )
                {
                    foreach ( $destinationIds["locationIds"] as $locationId )
                    {
                        if ( !isset( $locationIdToContentIdMapping[$locationId] ) )
                        {
                            $location = $this->repository->getLocationService()->loadLocation( $locationId );
                            $locationIdToContentIdMapping[$locationId] = $location->contentId;
                        }

                        $relations[$relationType][$locationIdToContentIdMapping[$locationId]] = true;
                    }
                }

                if ( isset( $destinationIds["contentIds"] ) )
                {
                    $relations[$relationType] += array_flip( $destinationIds["contentIds"] );
                }
            }
        }
    }

    /**
     * Persists relation data for a content version.
     *
     * This method creates new relations and deletes removed relations.
     *
     * @param array $inputRelations
     * @param mixed $sourceContentId
     * @param mixed $sourceContentVersionNo
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $existingRelations An array of existing relations for Content version (empty when creating new content)
     *
     * @return void
     */
    public function processFieldRelations(
        array $inputRelations,
        $sourceContentId,
        $sourceContentVersionNo,
        ContentType $contentType,
        array $existingRelations = array()
    )
    {
        // Map existing relations for easier handling
        $mappedRelations = array();
        foreach ( $existingRelations as $relation )
        {
            if ( $relation->type === Relation::FIELD )
            {
                $fieldDefinitionId = $contentType->getFieldDefinition( $relation->sourceFieldDefinitionIdentifier )->id;
                $mappedRelations[$relation->type][$fieldDefinitionId][$relation->destinationContentInfo->id] = $relation;
            }
            // Using bitwise AND as Legacy Stack stores COMMON, LINK and EMBED relation types
            // in the same entry using bitmask
            if ( $relation->type & Relation::LINK )
            {
                $mappedRelations[Relation::LINK][$relation->destinationContentInfo->id] = $relation;
            }
            if ( $relation->type & Relation::EMBED )
            {
                $mappedRelations[Relation::EMBED][$relation->destinationContentInfo->id] = $relation;
            }
        }

        // Add new relations
        foreach ( $inputRelations as $relationType => $relationData )
        {
            if ( $relationType === Relation::FIELD )
            {
                foreach ( $relationData as $fieldDefinitionId => $contentIds )
                {
                    foreach ( array_keys( $contentIds ) as $destinationContentId )
                    {
                        if ( isset( $mappedRelations[$relationType][$fieldDefinitionId][$destinationContentId] ) )
                        {
                            unset( $mappedRelations[$relationType][$fieldDefinitionId][$destinationContentId] );
                        }
                        else
                        {
                            $this->persistenceHandler->contentHandler()->addRelation(
                                new SPIRelationCreateStruct(
                                    array(
                                        "sourceContentId" => $sourceContentId,
                                        "sourceContentVersionNo" => $sourceContentVersionNo,
                                        "sourceFieldDefinitionId" => $fieldDefinitionId,
                                        "destinationContentId" => $destinationContentId,
                                        "type" => $relationType
                                    )
                                )
                            );
                        }
                    }
                }
            }
            else if ( $relationType === Relation::LINK || $relationType === Relation::EMBED )
            {
                foreach ( array_keys( $relationData ) as $destinationContentId )
                {
                    if ( isset( $mappedRelations[$relationType][$destinationContentId] ) )
                    {
                        unset( $mappedRelations[$relationType][$destinationContentId] );
                    }
                    else
                    {
                        $this->persistenceHandler->contentHandler()->addRelation(
                            new SPIRelationCreateStruct(
                                array(
                                    "sourceContentId" => $sourceContentId,
                                    "sourceContentVersionNo" => $sourceContentVersionNo,
                                    "sourceFieldDefinitionId" => null,
                                    "destinationContentId" => $destinationContentId,
                                    "type" => $relationType
                                )
                            )
                        );
                    }
                }
            }
        }

        // Remove relations not present in input set
        foreach ( $mappedRelations as $relationType => $relationData )
        {
            foreach ( $relationData as $relationEntry )
            {
                switch ( $relationType )
                {
                    case Relation::FIELD:
                        foreach ( $relationEntry as $relation )
                        {
                            $this->persistenceHandler->contentHandler()->removeRelation(
                                $relation->id,
                                $relationType
                            );
                        }
                        break;
                    case Relation::LINK:
                    case Relation::EMBED:
                        $this->persistenceHandler->contentHandler()->removeRelation(
                            $relationEntry->id,
                            $relationType
                        );
                }
            }
        }
    }
}
