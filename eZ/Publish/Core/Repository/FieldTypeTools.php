<?php
/**
 * File containing the eZ\Publish\Core\Repository\FieldTypeTools class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\FieldTypeTools as FieldTypeToolsInterface,
    eZ\Publish\API\Repository\Values\Content\VersionInfo,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * This class provides service methods available to FieldTypes
 *
 * This interface cannot be retrieved through the Public API and is not meant to be
 * used by its users. It is only available to {@link
 * eZ\Publish\SPI\FieldType\FieldType} implementors through the {@link
 * eZ\Publish\SPI\FieldType\FieldType::handleEvent()} method.
 *
 * @package eZ\Publish\API\Repository
 * @todo Rename to FieldTypeTools
 * @todo Add deleteRelation() method?
 */
class FieldTypeTools implements FieldTypeToolsInterface
{
    /**
     * Adds a relation
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
    public function addRelation( $relationType,
                                 VersionInfo $sourceVersion,
                                 $destinationContentId,
                                 FieldDefinition $fieldDefinition = null )
    {
        throw new NotImplementedException(
        // @todo Figure out if this is needed or not, there is a need for it in xmltext and relation field types to avoid using db directly for this
            __METHOD__ .
            " not implemented, & unclear if it should as relation handing is done in gateways in relation field types"
        );
    }
}
