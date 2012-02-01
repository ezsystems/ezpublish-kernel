<?php
namespace ezp\PublicAPI\Values\Content\Query\Criterion;
use ezp\PublicAPI\Values\Content\Query\Criterion,
    ezp\PublicAPI\Values\Content\Query\Criterion\Operator\Specifications,
    ezp\PublicAPI\Values\Content\Query\CriterionInterface;

/**
 * SectionId Criterion
 *
 * Will match content that belongs to one of the given sections
 */
class SectionId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Section criterion
     *
     * Matches the content against one or more sectionId
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of sectionId. $value must be an array of sectionId
     *        - Operator::EQ: match against a single sectionId. $value must be a single sectionId
     * @param integer|array(integer) One or more sectionId that must be matched
     *
     * @throws InvalidArgumentException if a non numeric id is given
     * @throws InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value  )
    {
        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
