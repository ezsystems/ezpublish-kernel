<?php

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

class LocationPriority extends Criterion implements CriterionInterface
{

    public function __construct( $operator, $value )
    {
        parent::__construct( null, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $operator, $value );
    }
}
