<?php
/**
 * File containing the ezp\Content\QueryBuilder class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/gnu_gpl GNU GPL v2
 * @version //autogentag//
 */

/**
 * This class provides a fluent interface to create a content query
 * @package ezp
 * @subpackage content
 *
 * @property-read CriterionFactory $field
 * @property-read CriterionFactory $metaData
 * @property-read CriterionFactory $contentId
 * @property-read CriterionFactory $contentType
 * @property-read CriterionFactory $contentTypeGroup
 * @property-read CriterionFactory $field
 * @property-read CriterionFactory $fulltext
 * @property-read CriterionFactory $location
 * @property-read CriterionFactory $permission
 * @property-read CriterionFactory $section
 * @property-read CriterionFactory $subTree
 * @property-read CriterionFactory $urlAlias
 */
namespace ezp\Content;
use ezp\Persistence;

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
     */
    public function __get( $property )
    {
        $class = "\\ezp\\Persistence\\Content\\Criterion\\" . ucfirst( $property );
        if ( !class_exists( $class ) )
        {
            throw new \InvalidArgumentException( "Criterion $class not found" );
        }
        return new CriterionFactory( $this, $class );
    }

    /**
     * Adds a new criterion to the list
     *
     * @param \ezp\content\Criteria\Criterion $c
     */
    public function addCriterion( \ezp\Persistence\Content\Criterion $c )
    {
        $this->query->criteria[] = $c;
    }
}
?>