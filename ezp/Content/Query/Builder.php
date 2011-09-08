<?php
/**
 * File containing the ezp\Content\Query\Builder class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Content\Query;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Query\SortClause,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\InvalidArgumentValue,
    InvalidArgumentException,
    ezp\Content\CriterionFactory,
    ezp\Content\Query;

/**
 * This class provides a fluent interface to create a content query
 *
 * Every Criterion is accessible using a getter on the Builder object.
 * For instance, calling $queryBuilder->field will return a CriterionFactory object for a Field Criterion.
 * This CriterionFactory will then give access to the operators the contained Criterion supports, as methods.
 * These methods can then be used to construct a new Criterion for the given target & value:
 *
 * <code>
 * $queryBuilder = new ezp\Content\Query\Builder();
 * $queryBuilder->addCriteria( $queryBuilder->contentType->eq( null, 'article' ) );
 * </code>
 *
 * @property-read \ezp\Content\CriterionFactory $field A new Field CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $metaData A new MetaData CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $dateMetadata A new DateMetadata CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $contentId A new ContentId CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $contentType A new ContentType CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $contentTypeGroup A new ContentTypeGroup CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $field A new field CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $fullText A new FullText CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $locationId A new LocationId CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $parentLocationId A new ParentLocationId CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $permission A new Permission CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $section A new Section CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $subtree A new Subtree CriterionFactory
 * @property-read \ezp\Content\CriterionFactory $urlAlias An new UrlAlias CriterionFactory
 *
 * @property-read \ezp\Content\CriterionFactory $or New logical OR criterion (alias for {@see $lOr})
 * @property-read \ezp\Content\CriterionFactory $lOr New logical OR criterion
 * @property-read \ezp\Content\CriterionFactory $and New logical AND criterion (alias for {@see $lAnd})
 * @property-read \ezp\Content\CriterionFactory $lAnd New logical AND criterion
 * @property-read \ezp\Content\CriterionFactory $not New logical NOT criterion (alias for {@see $lNot})
 * @property-read \ezp\Content\CriterionFactory $lNot
 */
class Builder
{
    public function __construct()
    {
        $this->sort = new SortClauseBuilder();
    }

    /**
     * Magic getter.
     * Gives access to criteria classes by means of their class name:
     * MetaData -> metadata
     * Field -> Field
     * A criterion factory
     *
     * @return \ezp\Persistence\Content\CriterionFactory
     */
    public function __get( $property )
    {
        $class = "ezp\\Persistence\\Content\\Criterion\\" . ucfirst( $property );
        if ( !class_exists( $class ) )
        {
            throw new InvalidArgumentException( "Criterion $class not found" );
        }
        return new CriterionFactory( $class );
    }

    /**
     * Adds new criteria to the list. As many parameters as possible can be provided.
     *
     * The given criteria will be added with a logical AND, meaning that they must all match.
     * To handle OR criteria, the {@see or}/{@see lOr} methods must be used.
     *
     * @param \ezp\Persistence\Content\Criterion..$ $c
     * @return \ezp\Content\Query\Builder
     */
    public function addCriteria( Criterion $c )
    {
        foreach ( func_get_args() as $arg )
        {
            if ( !$arg instanceof Criterion )
            {
                throw new InvalidArgumentException( "All arguments must be instances of ezp\Persistence\Content\Criterion" );
            }
            $this->criteria[] = $arg;
        }

        return $this;
    }

    /**
     * Logical or
     * Criterion: Criterion\LogicalAnd
     *
     * @param \ezp\Persistence\Content\Criterion $elementOne
     * @param \ezp\Persistence\Content\Criterion $elementTwo$...
     *
     * @return \ezp\Persistence\Content\Criterion\LogicalOr
     */
    public function lOr( Criterion $elementOne, Criterion $elementTwo )
    {
        $criterionFactory = new CriterionFactory( 'ezp\\Persistence\\Content\\Criterion\\LogicalOr' );
        return call_user_func_array( array( $criterionFactory, 'logicalOr' ), func_get_args() );
    }

    /**
     * Logical and
     * Criterion: Criterion\LogicalAnd
     *
     * @param \ezp\Persistence\Content\Criterion $elementOne
     * @param \ezp\Persistence\Content\Criterion $elementTwo$...
     *
     * @return \ezp\Persistence\Content\Criterion\LogicalAnd
     */
    public function lAnd( Criterion $elementOne, Criterion $elementTwo )
    {
        $criterionFactory = new CriterionFactory( 'ezp\\Persistence\\Content\\Criterion\\LogicalAnd' );
        return call_user_func_array( array( $criterionFactory, 'logicalAnd' ), func_get_args() );
    }

    /**
     * Logical not
     * Criterion: Criterion\LogicalNot
     *
     * @param \ezp\Persistence\Content\Criterion $criterion
     *
     * @return \ezp\Persistence\Content\Criterion\LogicalNot
     */
    public function not( Criterion $criterion )
    {
        $criterionFactory = new CriterionFactory( 'ezp\\Persistence\\Content\\Criterion\\LogicalNot' );
        return call_user_func_array( array( $criterionFactory, 'logicalNot' ), func_get_args() );
    }

    /**
     * Magic call method, used to provide or/and methods as an alternative to lOr/lAnd
     *
     * @param string $method
     * @param array $arguments
     *
     * @return \ezp\Persistence\Content\Criterion
     */
    public function __call( $method, $arguments )
    {
        switch ( $method )
        {
            case 'or':
                return call_user_func_array( array( $this, 'lOr' ), $arguments );
                break;

            case 'and':
                return call_user_func_array( array( $this, 'lAnd' ), $arguments );
                break;

            default:
                throw new PropertyNotFound( $method, __CLASS__ );
        }
    }

    /**
     * Adds new sorting clause objects to the query. One to many objects can be provided.
     *
     * @param \ezp\Persistence\Content\SortClause..$ $sortClause
     *
     * @return \ezp\Content\Query\Builder Self
     */
    public function addSortClause( SortClause $sortClause )
    {
        foreach ( func_get_args() as $arg )
        {
            if ( !$arg instanceof SortClause )
            {
                throw new InvalidArgumentException( "All arguments must be instances of ezp\Persistence\Content\Query\SortClause" );
            }
            $this->sortClauses[] = $arg;
        }

        return $this;
    }

    /**
     * Sets the query offset to $offset
     * @param int $offset
     * @return \ezp\Content\Query\Builder Self
     * @throws \ezp\Base\Exception\InvalidArgumentValue if $limit isn't an integer >= 0
     */
    public function setOffset( $offset )
    {
        if ( intval( $offset ) != $offset || $offset < 0 )
        {
            throw new InvalidArgumentValue( 'offset', $offset );
        }
        $this->offset = $offset;
    }

    /**
     * Sets the query offset to $limit.
     * A limit of 0 means no limit.
     *
     * @param int $offset
     * @return \ezp\Content\Query\Builder Self
     * @throws \ezp\Base\Exception\InvalidArgumentValue if $limit isn't an integer >= 0
     */
    public function setLimit( $limit )
    {
        if ( intval( $limit ) != $limit || $limit < 0 )
        {
            throw new InvalidArgumentValue( 'limit', $limit );
        }
        $this->limit = $limit;
    }

    /**
     * Returns the query
     * @return \ezp\Content\Query
     */
    public function getQuery()
    {
        $query = new Query;

        if ( count( $this->criteria ) > 0 )
        {
            // group all the criteria with a LogicalAnd
            if ( count( $this->criteria ) > 1 )
            {
                $query->criterion = call_user_func_array(
                    array( $this, 'and' ),
                    $this->criteria
                );
            }
            // directly inject the criterion
            else
            {
                $query->criterion = $this->criteria[0];
            }
        }

        $query->sortClauses = $this->sortClauses;
        $query->limit = $this->limit;
        $query->offset = $this->offset;

        return $query;
    }

    /**
     * The internal criteria array
     * @var Criterion[]
     */
    private $criteria = array();

    /**
     * SortClause objects array
     * @var \ezp\Persistence\Content\Query\SortClause[]
     */
    private $sortClauses = array();

    /**
     * Sort clause builder
     * @var \ezp\Content\Query\SortClauseBuilder
     */
    public $sort;

    /**
     * Query offset, starting from 0
     * @var int
     */
    public $offset = 0;

    /**
     * Query limit, as a number of items
     * @var int
     */
    public $limit = 0;
}
?>
