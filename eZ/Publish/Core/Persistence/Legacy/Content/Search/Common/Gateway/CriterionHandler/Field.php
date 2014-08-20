<?php
/**
 * File containing the DoctrineDatabase field criterion handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\FieldValue\Converter as FieldValueConverter;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use RuntimeException;

/**
 * Field criterion handler
 */
class Field extends CriterionHandler
{
    /**
     * DB handler to fetch additional field information
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Field converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldConverterRegistry;

    /**
     * Field value converter
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\FieldValue\Converter
     */
    protected $fieldValueConverter;

    /**
     * Transformation processor
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Construct from handler handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $fieldConverterRegistry
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriterionHandler\FieldValue\Converter $fieldValueConverter
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        Registry $fieldConverterRegistry,
        FieldValueConverter $fieldValueConverter,
        TransformationProcessor $transformationProcessor
    )
    {
        $this->dbHandler = $dbHandler;
        $this->fieldConverterRegistry = $fieldConverterRegistry;
        $this->fieldValueConverter = $fieldValueConverter;
        $this->transformationProcessor = $transformationProcessor;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\Field;
    }

    /**
     * Returns relevant field information for the specified field
     *
     * The returned information is returned as an array of the attribute
     * identifier and the sort column, which should be used.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given $fieldIdentifier.
     * @throws \RuntimeException if no converter is found
     *
     * @caching
     * @param string $fieldIdentifier
     *
     * @return array
     */
    protected function getFieldsInformation( $fieldIdentifier )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'id', 'ezcontentclass_attribute' ),
                $this->dbHandler->quoteColumn( 'data_type_string', 'ezcontentclass_attribute' )
            )
            ->from(
                $this->dbHandler->quoteTable( 'ezcontentclass_attribute' )
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass_attribute' ),
                        $query->bindValue( $fieldIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'is_searchable', 'ezcontentclass_attribute' ),
                        $query->bindValue( 1, null, \PDO::PARAM_INT )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        if ( !( $rows = $statement->fetchAll( \PDO::FETCH_ASSOC ) ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$fieldIdentifier}'."
            );
        }

        $fieldMapArray = array();
        foreach ( $rows as $row )
        {
            if ( !isset( $fieldMapArray[ $row['data_type_string'] ] ) )
            {
                $converter = $this->fieldConverterRegistry->getConverter( $row['data_type_string'] );

                if ( !$converter instanceof Converter )
                {
                    throw new RuntimeException(
                        "getConverter({$row['data_type_string']}) did not return a converter, got: " .
                        gettype( $converter )
                    );
                }

                $fieldMapArray[ $row['data_type_string'] ] = array(
                    'ids' => array(),
                    'column' => $converter->getIndexColumn(),
                );
            }

            $fieldMapArray[ $row['data_type_string'] ]['ids'][] = $row['id'];
        }

        return $fieldMapArray;
    }

    /**
     * Generate query expression for a Criterion this handler accepts
     *
     * accept() must be called before calling this method.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle( CriteriaConverter $converter, SelectQuery $query, Criterion $criterion )
    {
        $fieldsInformation = $this->getFieldsInformation( $criterion->target );

        $subSelect = $query->subSelect();
        $subSelect->select(
            $this->dbHandler->quoteColumn( 'contentobject_id' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        );

        $whereExpressions = array();
        foreach ( $fieldsInformation as $fieldTypeIdentifier => $fieldsInfo )
        {
            if ( $fieldsInfo['column'] === false )
            {
                throw new NotImplementedException(
                    "A field of type '{$fieldTypeIdentifier}' is not searchable in the legacy search engine."
                );
            }

            $filter = $this->fieldValueConverter->convertCriteria(
                $fieldTypeIdentifier,
                $subSelect,
                $criterion,
                $fieldsInfo['column']
            );

            $whereExpressions[] = $subSelect->expr->lAnd(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn( 'contentclassattribute_id' ),
                    $fieldsInfo['ids']
                ),
                $filter
            );
        }

        $subSelect->where(
            $subSelect->expr->lAnd(
                $subSelect->expr->eq(
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_attribute' ),
                    $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
                ),
                // Join conditions with a logical OR if several conditions exist
                count( $whereExpressions ) > 1 ? $subSelect->expr->lOr( $whereExpressions ) : $whereExpressions[0]
            )
        );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}
