<?php

/**
 * File containing the DoctrineDatabase FieldRelation criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use RuntimeException;

/**
 * FieldRelation criterion handler.
 */
class FieldRelation extends FieldBase
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FieldRelation;
    }

    /**
     * Returns a list of IDs of searchable FieldDefinitions for the given criterion target.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getFieldDefinitionsIds($fieldDefinitionIdentifier)
    {
        $fieldDefinitionIdList = [];
        $fieldMap = $this->contentTypeHandler->getSearchableFieldMap();

        foreach ($fieldMap as $contentTypeIdentifier => $fieldIdentifierMap) {
            // First check if field exists in the current ContentType, there is nothing to do if it doesn't
            if (!isset($fieldIdentifierMap[$fieldDefinitionIdentifier])) {
                continue;
            }

            $fieldDefinitionIdList[] = $fieldIdentifierMap[$fieldDefinitionIdentifier]['field_definition_id'];
        }

        if (empty($fieldDefinitionIdList)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable fields found for the given criterion target '{$fieldDefinitionIdentifier}'."
            );
        }

        return $fieldDefinitionIdList;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @throws \RuntimeException
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        $column = $this->dbHandler->quoteColumn('to_contentobject_id', 'ezcontentobject_link');
        $fieldDefinitionIds = $this->getFieldDefinitionsIds($criterion->target);

        switch ($criterion->operator) {
            case Criterion\Operator::CONTAINS:
                if (count($criterion->value) > 1) {
                    $subRequest = [];

                    foreach ($criterion->value as $value) {
                        $subSelect = $query->subSelect();

                        $subSelect->select(
                            $this->dbHandler->quoteColumn('from_contentobject_id')
                        )->from(
                            $this->dbHandler->quoteTable('ezcontentobject_link')
                        );

                        $subSelect->where(
                            $subSelect->expr->lAnd(
                                $subSelect->expr->eq(
                                    $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link'),
                                    $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                                ),
                                $subSelect->expr->in(
                                    $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_link'),
                                    $fieldDefinitionIds
                                ),
                                $subSelect->expr->eq(
                                    $column,
                                    $value
                                )
                            )
                        );

                        $subRequest[] = $subSelect->expr->in(
                            $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                            $subSelect
                        );
                    }

                    return $query->expr->lAnd(
                        $subRequest
                    );
                }
                // Intentionally omitting break

            case Criterion\Operator::IN:
                $subSelect = $query->subSelect();

                $subSelect->select(
                    $this->dbHandler->quoteColumn('from_contentobject_id')
                )->from(
                    $this->dbHandler->quoteTable('ezcontentobject_link')
                );

                return $query->expr->in(
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                    $subSelect->where(
                        $subSelect->expr->lAnd(
                            $subSelect->expr->eq(
                                $this->dbHandler->quoteColumn('from_contentobject_version', 'ezcontentobject_link'),
                                $this->dbHandler->quoteColumn('current_version', 'ezcontentobject')
                            ),
                            $subSelect->expr->in(
                                $this->dbHandler->quoteColumn('contentclassattribute_id', 'ezcontentobject_link'),
                                $fieldDefinitionIds
                            ),
                            $subSelect->expr->in(
                                $column,
                                $criterion->value
                            )
                        )
                    )
                );

            default:
                throw new RuntimeException("Unknown operator '{$criterion->operator}' for RelationList criterion handler.");
        }
    }
}
