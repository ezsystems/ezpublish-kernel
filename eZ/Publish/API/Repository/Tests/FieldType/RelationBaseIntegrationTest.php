<?php
/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\RelationBaseIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;

use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * Base integration test for field types handling content relations.
 *
 * @group integration
 * @group field-type
 * @group relation
 */
abstract class RelationBaseIntegrationTest extends BaseIntegrationTest
{
    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    abstract public function getCreateExpectedRelations( Content $content );

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    abstract public function getUpdateExpectedRelations( Content $content );

    /**
     * Tests relation processing on field create.
     */
    public function testCreateContentRelationsProcessedCorrect()
    {
        $content = $this->createContent( $this->getValidCreationFieldData() );

        $this->assertEquals(
            $this->normalizeRelations(
                $this->getCreateExpectedRelations( $content )
            ),
            $this->normalizeRelations(
                $this->getRepository()->getContentService()->loadRelations( $content->versionInfo )
            )
        );
    }

    /**
     * Tests relation processing on field update.
     */
    public function testUpdateContentRelationsProcessedCorrect()
    {
        $content = $this->updateContent( $this->getValidUpdateFieldData() );

        $this->assertEquals(
            $this->normalizeRelations(
                $this->getUpdateExpectedRelations( $content )
            ),
            $this->normalizeRelations(
                $this->getRepository()->getContentService()->loadRelations( $content->versionInfo )
            )
        );
    }

    /**
     * Normalizes given $relations for easier comparison.
     *
     * @param \eZ\Publish\Core\Repository\Values\Content\Relation[] $relations
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Relation[]
     */
    protected function normalizeRelations( array $relations )
    {
        usort(
            $relations,
            function ( Relation $a, Relation $b )
            {
                if ( $a->type == $b->type )
                {
                    return $a->destinationContentInfo->id < $b->destinationContentInfo->id ? 1 : -1;
                }
                return $a->type < $b->type ? 1 : -1;
            }
        );
        $normalized = array_map(
            function ( Relation $relation )
            {
                $newRelation = new Relation(
                    array(
                        "id" => null,
                        "sourceFieldDefinitionIdentifier" => $relation->sourceFieldDefinitionIdentifier,
                        "type" => $relation->type,
                        "sourceContentInfo" => $relation->sourceContentInfo,
                        "destinationContentInfo" => $relation->destinationContentInfo
                    )
                );
                return $newRelation;
            },
            $relations
        );

        return $normalized;
    }
}
