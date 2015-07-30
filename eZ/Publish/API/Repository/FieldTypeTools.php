<?php

/**
 * File containing the eZ\Publish\API\Repository\FieldTypeTools class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * This class provides service methods available to FieldTypes.
 *
 * This interface cannot be retrieved through the Public API and is not meant to be
 * used by its users. It is only available to {@link * eZ\Publish\SPI\FieldType\EventListener} implementers through the {@link * eZ\Publish\SPI\FieldType\EventListener::handleEvent()} method.
 *
 * @todo Change this to be able to handle relations for FieldTypes in an effective manner
 */
interface FieldTypeTools
{
    /**
     * Adds a relation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $relationType has an unsupported value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If there is a mismatch between $relationType and provided values.
     *
     * The source of the relation is the content and version
     * referenced by $sourceVersion.
     *
     * @param int $relationType One of Relation::COMMON, Relation::EMBED, Relation::LINK or Relation::FIELD
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion
     * @param mixed $destinationContentId
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null $fieldDefinition
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation The newly created relation
     */
    public function addRelation(
        $relationType,
        VersionInfo $sourceVersion,
        $destinationContentId,
        FieldDefinition $fieldDefinition = null
    );
}
