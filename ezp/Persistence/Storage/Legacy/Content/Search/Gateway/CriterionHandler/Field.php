<?php
/**
 * File containing the EzcDatabase field criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler,
    ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriteriaConverter,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Base\Exception;

/**
 * Field criterion handler
 */
class Field extends CriterionHandler
{
    /**
     * DB handler to fetch additional field information
     *
     * @var \ezp\Persistence\Storage\Legacy\EzcDatabase
     */
    protected $dbHandler;

    /**
     * Field converter registry
     *
     * @var Converter\Registry
     */
    protected $fieldConverterRegistry;

    /**
     * Construct from handler handler
     *
     * @param EzcDbHandler $dbHandler
     * @param Converter\Registry $fieldConverterRegistry
     * @return void
     */
    public function __construct( EzcDbHandler $dbHandler, Converter\Registry $fieldConverterRegistry )
    {
        $this->dbHandler              = $dbHandler;
        $this->fieldConverterRegistry = $fieldConverterRegistry;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
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
     * @caching
     * @param Criterion\FieldIdentifierStruct $target
     * @return array
     */
    protected function getFieldInformation( Criterion\FieldIdentifierStruct $target )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->quoteColumn( 'id', 'ezcontentclass_attribute' ),
                $this->dbHandler->quoteColumn( 'data_type_string', 'ezcontentclass_attribute' )
            )
            ->from(
                $this->dbHandler->quoteTable( 'ezcontentclass' ),
                $this->dbHandler->quoteTable( 'ezcontentclass_attribute' )
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentclass_id', 'ezcontentclass_attribute' ),
                        $this->dbHandler->quoteColumn( 'id', 'ezcontentclass' )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass' ),
                        $query->bindValue( $target->contentTypeIdentifier )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'identifier', 'ezcontentclass_attribute' ),
                        $query->bindValue( $target->fieldIdentifier )
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        if ( !( $row = $statement->fetch( \PDO::FETCH_ASSOC ) ) )
        {
            throw new Exception\NotFound( 'Content type', $target->contentTypeIdentifier . '/' . $target->fieldIdentifier );
        }

        $converter =  $this->fieldConverterRegistry->getConverter( $row['data_type_string'] );

        return array(
            'id'     => $row['id'],
            'column' => $converter->getIndexColumn(),
        );
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion )
    {
        $fieldInformation = $this->getFieldInformation( $criterion->target );
        $column           = $fieldInformation['column'];

        $subSelect = $query->subSelect();
        $subSelect
            ->select( 'contentobject_id' )
            ->from( 'ezcontentobject_attribute' );

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                $filter = $subSelect->expr->in( $column, $criterion->value );
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
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $filter = $subSelect->expr->$operatorFunction(
                    $column,
                    $subSelect->bindValue( $criterion->value )
                );
                break;

            default:
                throw new \RuntimeException( 'Unknown operator.' );
        }

        $subSelect->where( $subSelect->expr->lAnd(
            $subSelect->expr->eq(
                'contentclassattribute_id',
                $subSelect->bindValue( $fieldInformation['id'] )
            ),
            $filter
        ) );

        return $query->expr->in( 'id', $subSelect );

    }
}

