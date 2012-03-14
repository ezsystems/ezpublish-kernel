<?php
/**
 * File containing the internal FieldTypeService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType;
use ezp\Base\Service as BaseService,
    ezp\Content\Relation,
    ezp\Content\Proxy,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct,
    ezp\Base\Exception\Logic;

/**
 * Service providing necessary functionality for field types to perform internal content operations.
 */
class Service extends BaseService
{

    /**
     * Creates a new relation entry.
     *
     * This method will only be used by field types.
     *
     * @access private
     * @internal
     * @param int $relationType The type of relation to create.
     * @param mixed $contentFromId The content id you want where you want to add
     *                             the relation.
     * @param mixed $versionFromNo The version number where we are adding this relation.
     * @param mixed $contentToId The destination of the relation, which content
     *                           id we are relating to.
     * @param null|mixed $fieldDefinitionId The id of the field definition that
     *                                      holds the attribute level relation.
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $relationType has an unsupported value
     * @throws \ezp\Base\Exception\Logic If there is a mismatch between $relationType and provided values.
     * @return \ezp\Content\Relation
     */
    public function addRelation( $relationType, $contentFromId, $versionFromNo, $contentToId, $fieldDefinitionId = null )
    {
        // Creating the Relation object to be returned here, in order insert
        // proxied destination Content
        // @todo If ezp\Content\Relation is refactored wrt. the internal
        //       destination Content then this method can likely be refactored to, to fully create the Relation from creation struct.
        $relation = new Relation( $relationType, new Proxy( $contentToId, $this->repository->getContentService() ) );

        if ( $relationType === Relation::ATTRIBUTE && $fieldDefinitionId === null )
        {
            throw new Logic( 'addRelation', 'Attribute level relation requested, but no field definition id given.');
        }

        // We grab the remaining values for the creation struct from the created object.
        $creationStruct = $this->fillStruct(
            new RelationCreateStruct(
                array(
                     'sourceContentId' => $contentFromId,
                     'sourceContentVersionNo' => $versionFromNo,
                     'sourceFieldDefinitionId' => $fieldDefinitionId ?: 0
                )
            ),
            $relation
        );

        // Finally we refresh the object with state from the stored entity.
        return $relation->setState(
            array(
                'properties' => $this->handler->contentHandler()->addRelation( $creationStruct )
            )
        );
    }
}
