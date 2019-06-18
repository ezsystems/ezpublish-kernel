<?php

/**
 * File containing the RelationProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as SPIRelationCreateStruct;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * RelationProcessor is an internal service used for handling field relations upon Content creation or update.
 *
 * @internal Meant for internal use by Repository.
 */
class RelationProcessor
{
    use LoggerAwareTrait;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     */
    public function __construct(Handler $handler)
    {
        $this->persistenceHandler = $handler;
        $this->logger = new NullLogger();
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
     */
    public function appendFieldRelations(
        array &$relations,
        array &$locationIdToContentIdMapping,
        SPIFieldType $fieldType,
        BaseValue $fieldValue,
        $fieldDefinitionId
    ) {
        foreach ($fieldType->getRelations($fieldValue) as $relationType => $destinationIds) {
            if ($relationType === Relation::FIELD) {
                if (!isset($relations[$relationType][$fieldDefinitionId])) {
                    $relations[$relationType][$fieldDefinitionId] = [];
                }
                $relations[$relationType][$fieldDefinitionId] += array_flip($destinationIds);
            } elseif ($relationType & (Relation::LINK | Relation::EMBED)) {
                // Using bitwise operators as Legacy Stack stores COMMON, LINK and EMBED relation types
                // in the same entry using bitmask
                if (!isset($relations[$relationType])) {
                    $relations[$relationType] = [];
                }

                if (isset($destinationIds['locationIds'])) {
                    foreach ($destinationIds['locationIds'] as $locationId) {
                        try {
                            if (!isset($locationIdToContentIdMapping[$locationId])) {
                                $location = $this->persistenceHandler->locationHandler()->load($locationId);
                                $locationIdToContentIdMapping[$locationId] = $location->contentId;
                            }

                            $relations[$relationType][$locationIdToContentIdMapping[$locationId]] = true;
                        } catch (NotFoundException $e) {
                            $this->logger->error('Invalid relation: destination location not found', [
                                'fieldDefinitionId' => $fieldDefinitionId,
                                'locationId' => $locationId,
                            ]);
                        }
                    }
                }

                if (isset($destinationIds['contentIds'])) {
                    $relations[$relationType] += array_flip($destinationIds['contentIds']);
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
     */
    public function processFieldRelations(
        array $inputRelations,
        $sourceContentId,
        $sourceContentVersionNo,
        ContentType $contentType,
        array $existingRelations = []
    ) {
        // Map existing relations for easier handling
        $mappedRelations = [];
        foreach ($existingRelations as $relation) {
            if ($relation->type === Relation::FIELD) {
                $fieldDefinition = $contentType->getFieldDefinition($relation->sourceFieldDefinitionIdentifier);
                if ($fieldDefinition !== null) {
                    $mappedRelations[$relation->type][$fieldDefinition->id][$relation->destinationContentInfo->id] = $relation;
                }
            }
            // Using bitwise AND as Legacy Stack stores COMMON, LINK and EMBED relation types
            // in the same entry using bitmask
            if ($relation->type & Relation::LINK) {
                $mappedRelations[Relation::LINK][$relation->destinationContentInfo->id] = $relation;
            }
            if ($relation->type & Relation::EMBED) {
                $mappedRelations[Relation::EMBED][$relation->destinationContentInfo->id] = $relation;
            }
        }

        // Add new relations
        foreach ($inputRelations as $relationType => $relationData) {
            if ($relationType === Relation::FIELD) {
                foreach ($relationData as $fieldDefinitionId => $contentIds) {
                    foreach (array_keys($contentIds) as $destinationContentId) {
                        if (isset($mappedRelations[$relationType][$fieldDefinitionId][$destinationContentId])) {
                            unset($mappedRelations[$relationType][$fieldDefinitionId][$destinationContentId]);
                        } else {
                            $this->persistenceHandler->contentHandler()->addRelation(
                                new SPIRelationCreateStruct(
                                    [
                                        'sourceContentId' => $sourceContentId,
                                        'sourceContentVersionNo' => $sourceContentVersionNo,
                                        'sourceFieldDefinitionId' => $fieldDefinitionId,
                                        'destinationContentId' => $destinationContentId,
                                        'type' => $relationType,
                                    ]
                                )
                            );
                        }
                    }
                }
            } elseif ($relationType === Relation::LINK || $relationType === Relation::EMBED) {
                foreach (array_keys($relationData) as $destinationContentId) {
                    if (isset($mappedRelations[$relationType][$destinationContentId])) {
                        unset($mappedRelations[$relationType][$destinationContentId]);
                    } else {
                        $this->persistenceHandler->contentHandler()->addRelation(
                            new SPIRelationCreateStruct(
                                [
                                    'sourceContentId' => $sourceContentId,
                                    'sourceContentVersionNo' => $sourceContentVersionNo,
                                    'sourceFieldDefinitionId' => null,
                                    'destinationContentId' => $destinationContentId,
                                    'type' => $relationType,
                                ]
                            )
                        );
                    }
                }
            }
        }

        // Remove relations not present in input set
        foreach ($mappedRelations as $relationType => $relationData) {
            foreach ($relationData as $relationEntry) {
                switch ($relationType) {
                    case Relation::FIELD:
                        foreach ($relationEntry as $relation) {
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
