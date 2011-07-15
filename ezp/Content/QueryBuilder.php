<?php
/**
 * File containing the ezp\Content\QueryBuilder class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Persistence\Content\Criterion,
    ezp\Base\Exception\PropertyNotFound,
    InvalidArgumentException;

/**
 * This class provides a fluent interface to create a content query
 *
 *
 * <code>
 * @todo write
 * </code>
 *
 * @property-read ezp\Content\CriterionFactory $field
 * @property-read ezp\Content\CriterionFactory $metaData
 * @property-read ezp\Content\CriterionFactory $contentId
 * @property-read ezp\Content\CriterionFactory $contentType
 * @property-read ezp\Content\CriterionFactory $contentTypeGroup
 * @property-read ezp\Content\CriterionFactory $field
 * @property-read ezp\Content\CriterionFactory $fulltext
 * @property-read ezp\Content\CriterionFactory $location
 * @property-read ezp\Content\CriterionFactory $permission
 * @property-read ezp\Content\CriterionFactory $section
 * @property-read ezp\Content\CriterionFactory $subTree
 * @property-read ezp\Content\CriterionFactory $urlAlias
 *
 * @property-read ezp\Content\CriterionFactory $or New logical OR criterion (alias for {@see $lOr})
 * @property-read ezp\Content\CriterionFactory $lOr New logical OR criterion
 * @property-read ezp\Content\CriterionFactory $and New logical AND criterion (alias for {@see $lAnd})
 * @property-read ezp\Content\CriterionFactory $lAnd New logical AND criterion
 * @property-read ezp\Content\CriterionFactory $not New logical NOT criterion (alias for {@see $lNot})
 * @property-read ezp\Content\CriterionFactory $lNot
 */
class QueryBuilder
{
    /**
     * The query that is being built
     * @var ezp\Content\Query
     */
    protected $query;

    public function __construct()
    {
        $this->query = new Query;
    }

    /**
     * Magic getter.
     * Gives access to criteria classes by means of their class name:
     * MetaData -> metadata
     * Field -> Field
     * A criterion factory
     *
     * @return ezp\Persistence\Content\CriterionFactory
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
     * @param ezp\content\Criteria\Criterion$... $c
     */
    public function addCriteria( Criterion $c )
    {
        foreach ( func_get_args() as $arg )
        {
            if ( !$arg instanceof Criterion )
            {
                throw new InvalidArgumentException( "All arguments must be instances of ezp\Persistence\Content\Criterion" );
            }
            $this->query->criteria[] = $arg;
        }
    }

    /**
     * Logical or
     * Criterion: Criterion\LogicalAnd
     *
     * @param Criterion $elementOne
     * @param Criterion $elementTwo$...
     *
     * @return ezp\Persistence\Content\Criterion\LogicalOr
     */
    public function lOr( Criterion $elementOne, Criterion $elementTwo )
    {
        $criterionFactory = new CriterionFactory( 'ezp\Persistence\Content\Criterion\LogicalOr' );
        return call_user_func_array( array( $criterionFactory, 'logicalOr' ), func_get_args() );
    }

    /**
     * Logical and
     * Criterion: Criterion\LogicalAnd
     *
     * @param Criterion $elementOne
     * @param Criterion $elementTwo$...
     *
     * @return ezp\Persistence\Content\Criterion\LogicalAnd
     */
    public function lAnd( Criterion $elementOne, Criterion $elementTwo )
    {
        $criterionFactory = new CriterionFactory( 'ezp\Persistence\Content\Criterion\LogicalAnd' );
        return call_user_func_array( array( $criterionFactory, 'logicalAnd' ), func_get_args() );
    }

    /**
     * Logical not
     * Criterion: Criterion\LogicalNot
     *
     * @param Criterion $criterion
     *
     * @return ezp\Persistence\Content\Criterion\LogicalNot
     */
    public function not( Criterion $criterion )
    {
        $criterionFactory = new CriterionFactory( 'ezp\Persistence\Content\Criterion\LogicalNot' );
        return call_user_func_array( array( $criterionFactory, 'logicalNot' ), func_get_args() );
    }

    /**
     * Magic call method, used to provide or/and methods as an alternative to lOr/lAnd
     *
     * @param string $method
     * @param array $arguments
     *
     * @return ezp\Persistence\Content\Criterion
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
}
?>
