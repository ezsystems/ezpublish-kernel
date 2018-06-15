# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* The abstract class `eZ\Publish\API\Repository\Values\Content\Query\Criterion` now implements interface `eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface`.

* The interface `eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface` no longer specifies `createFromQueryBuilder` and `getSpecifications` methods. The former was already specified by `eZ\Publish\API\Repository\Values\Content\Query\Criterion` abstract class and the latter was moved there.

* Classes extending the abstract class `eZ\Publish\API\Repository\Values\Content\Query\Criterion` no longer directly implement interface `eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface`. This interface is still implemented in those classes via `eZ\Publish\API\Repository\Values\Content\Query\Criterion` abstract class.

* The abstract class `eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator` now throws `eZ\Publish\API\Repository\Exceptions\NotImplementedException` for method `getSpecifications`. This method will be completely removed in 8.0 when `LogicalOperator` no longer extends `eZ\Publish\API\Repository\Values\Content\Query\Criterion`.

## Deprecations

* The method `createFromQueryBuilder` from the abstract class `eZ\Publish\API\Repository\Values\Content\Query\Criterion` is deprecated and will be removed in 8.0. All overrides of this method are also deprecated and will be removed in 8.0.
Call constructors directly instead.

## Removed features
