<?php
/**
 * File containing FieldCriteria class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

class FieldCriteria extends Criteria implements CriteriaInterface
{
    /**
     * Field name to filter on
     * @var string
     */
    protected $fieldName;

    /**
     * Field value for filtering
     * @var string|int|array(string)
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    protected $fieldValue;

    /**
     * Operator to be used for filter condition
     * @var string
     */
    protected $operator;


    public function __construct( $fieldName )
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Adds an equals condition (==)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function eq( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_EQUALS;
    }

    /**
     * Adds a not equals condition (!=)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function neq( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_NOT_EQUALS;
    }

	/**
     * Adds a like condition
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function like( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LIKE;
    }

	/**
     * Adds a greater than condition (>)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function gt( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_GREATER_THAN;
    }

	/**
     * Adds a greater than or equals condition (>=)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function gte( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_GREATER_THAN_EQUALS;
    }

	/**
     * Adds a lower than condition (<)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function lt( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LOWER_THAN;
    }

	/**
     * Adds a lower than or equals condition (<=)
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function lte( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LOWER_THAN_EQUALS;
    }

	/**
     * Adds a between condition
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function between( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_BETWEEN;
    }

	/**
     * Adds a not between condition
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function notBetween( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_NOT_BETWEEN;
    }

	/**
     * Adds a IN condition
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function in( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_IN;
    }

	/**
     * Adds a NOT IN condition
     * @param string|int $fieldValue Value to match
     * @todo Should this accept any type ? The FieldType would be responsible to give a proper representation
     */
    public function notIn( $fieldValue )
    {
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_NOT_IN;
    }

    /**
     * @see CriteriaInterface::toHash()
     */
    public function toHash()
    {
        $dto = new CriteriaDTO();
        $dto->criteriaFilterName = $this->fieldName;
        $dto->criteriaFilterValue = $this->fieldValue;
        $dto->criteriaFilterOperator = $this->operator;

        return $dto;
    }
}
?>
