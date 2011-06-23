<?php
/**
 * File containing MetadataCriteria class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

class MetadataCriteria extends Criteria implements CriteriaInterface
{
    /**
     * Field name to filter on
     * @var string
     */
    protected $fieldName;

    /**
     * Field value for filtering
     * @var mixed
     */
    protected $fieldValue;

    /**
     * Operator to be used for filter condition
     * @var string
     */
    protected $operator;


    public function __construct()
    {

    }

    /**
     * Adds an equals condition (==)
     * @param string $fieldName Field name to filter from. Format is <content_type_identifier>/<field_identifier>
     * @param mixed $fieldValue Value to match
     */
    public function eq( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_EQUALS;
    }

    /**
     * Adds a not equals condition (!=)
     * @param mixed $fieldValue Value to match
     */
    public function neq( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_NOT_EQUALS;
    }

	/**
     * Adds a like condition
     * @param mixed $fieldValue Value to match
     */
    public function like( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LIKE;
    }

	/**
     * Adds a greater than condition (>)
     * @param mixed $fieldValue Value to match
     */
    public function gt( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_GREATER_THAN;
    }

	/**
     * Adds a greater than or equals condition (>=)
     * @param mixed $fieldValue Value to match
     */
    public function gte( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_GREATER_THAN_EQUALS;
    }

	/**
     * Adds a lower than condition (<)
     * @param mixed $fieldValue Value to match
     */
    public function lt( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LOWER_THAN;
    }

	/**
     * Adds a lower than or equals condition (<=)
     * @param mixed $fieldValue Value to match
     */
    public function lte( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_LOWER_THAN_EQUALS;
    }

	/**
     * Adds a between condition
     * @param mixed $fieldValue Value to match
     */
    public function between( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_BETWEEN;
    }

	/**
     * Adds a not between condition
     * @param mixed $fieldValue Value to match
     */
    public function notBetween( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_NOT_BETWEEN;
    }

	/**
     * Adds a IN condition
     * @param mixed $fieldValue Value to match
     */
    public function in( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operator = self::OP_IN;
    }

	/**
     * Adds a NOT IN condition
     * @param mixed $fieldValue Value to match
     */
    public function notIn( $fieldName, $fieldValue )
    {
        $this->fieldName = $fieldName;
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
