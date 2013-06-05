<?php
/**
 * File containing the EzcDatabase field criterion handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use ezcQuerySelect;
use RuntimeException;

/**
 * Field criterion handler
 */
class Field extends CriterionHandler
{
    /**
     * DB handler to fetch additional field information
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Field converter registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldConverterRegistry;

    /**
     * Construct from handler handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $fieldConverterRegistry
     */
    public function __construct( EzcDbHandler $dbHandler, Registry $fieldConverterRegistry )
    {
        $this->dbHandler = $dbHandler;
        $this->fieldConverterRegistry = $fieldConverterRegistry;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
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
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter$converter
     * @param \ezcQuerySelect $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion$criterion
     *
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, ezcQuerySelect $query, Criterion $criterion )
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

            $column = $this->dbHandler->quoteColumn( $fieldsInfo['column'] );
            switch ( $criterion->operator )
            {
                case Criterion\Operator::IN:
                    $filter = $subSelect->expr->in(
                        $column,
                        $criterion->value
                    );
                    break;

                case Criterion\Operator::BETWEEN:
                    $filter = $subSelect->expr->between(
                        $column,
                        $subSelect->bindValue( $criterion->value[0] ),
                        $subSelect->bindValue( $criterion->value[1] )
                    );
                    break;

                case Criterion\Operator::EQ:
                case Criterion\Operator::GT:
                case Criterion\Operator::GTE:
                case Criterion\Operator::LT:
                case Criterion\Operator::LTE:
                case Criterion\Operator::LIKE:
                    $operatorFunction = $this->comparatorMap[$criterion->operator];
                    $filter = $subSelect->expr->$operatorFunction(
                        $column,
                        $subSelect->bindValue( $criterion->value )
                    );
                    break;

                default:
                    throw new RuntimeException( 'Unknown operator.' );
            }

            $whereExpressions[] = $subSelect->expr->lAnd(
                $subSelect->expr->in(
                    $this->dbHandler->quoteColumn( 'contentclassattribute_id' ),
                    $fieldsInfo['ids']
                ),
                $filter
            );
        }

        if ( isset( $whereExpressions[1] ) )
            $subSelect->where( $subSelect->expr->lOr( $whereExpressions ) );
        else
            $subSelect->where( $whereExpressions[0] );

        return $query->expr->in(
            $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
            $subSelect
        );
    }
}

