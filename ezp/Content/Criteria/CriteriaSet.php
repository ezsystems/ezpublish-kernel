<?php
/**
 * File containing ezp\Content\Criteria\CriteriaCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;
use ezp\Content;

/**
 * Criteria set to be used to find a content in a subtree
 * Example :
 * <code>
 * use ezp\Content\Repository as ContentRepository;
 *
 * $contentService = ContentRepository::get()->getContentService();
 * $c = $contentService->createCriteria();
 * $c->where( // andCondition() is implicit
 *    $c->location->isChildOf( $parentLocation ), // Direct children
 *    // $c->location->subTree( $parentLocation ), // Recursive
 *    $c->type( "folder" ),
 *    $c->field->eq( "name", "Another name" ),
 *    $c->meta->gte( "published", new DateTime( "yesterday" ) ),
 * )
 * ->limit( 5 ),
 * ->offset( 0 );
 * $result = $contentService->find( $c );
 * </code>
 *
 * @property-read FieldCriteria $field The field criteria
 * @property-read MetadataCriteria $meta Metadata criteria (published date, section, path, owner...)
 * @property-read LocationCriteria $location Location criteria for subtree filtering (most likely starting point)
 */
class CriteriaSet
{
    /**
     * Parent location to retrieve content from
     * @var ezp\Content\Location
     */
    protected $parentLocation;

    /**
     * Parent location Id to retrieve content from
     * @var integer
     */
    protected $parentLocationId;

    /**
     * Criterias for content retrieval filtering
     * @var Criteria[]|Criterion[] )
     */
    protected $criterias = array();

    /**
     * Clauses for content sorting
     * @var array(SortByClause)
     */
    protected $sortItems = array();

    /**
     * Maximum number of results that need to be returned by the query
     * @var integer
     */
    protected $limit;

    /**
     * Which row that will be first returned in result (starting from 0)
     * @var integer
     */
    protected $offset;

    /**
     * Content type identifier
     * @var string
     */
    protected $type;

    /**
     * Logic expression object for this criteria collection for AND/OR association of criterias
     * @var LogicExpression
     */
    public $logic;

    /**
     * Constructs a new CriteriaCollection
     */
    public function __construct()
    {
        $this->logic = new LogicExpression();
    }

    /**
     * Generic getter, that instantiates a type of criteria
     *
     * @param string $criteriaName field, meta, location, sortByMeta, sortByField
     *
     * @return Criteria
     */
    public function __get( $criteriaName )
    {
        switch ( $criteriaName )
        {
            case "field":
                return new FieldCriteria();

            case "meta":
                return new MetadataCriteria();

            case "location":
                return new LocationCriteria();

            default:
                throw \InvalidArgumentException( "Criteria '{$criteriaName}' is not supported" );
        }
    }

    /**
     * Adds all the given criterias to the query
     *
     * All arguments must be valid Criteria or Criterion objects
     *
     * @param Criteria|Criterion $criteriaOne
     *
     * @return CriteriaCollection
     * @throws \InvalidArgumentException If at least one of the arguments is not a valid Criteria/Criterion object
     */
    public function where( $criteriaOne )
    {
        foreach ( func_get_args() as $c )
        {
            if ( !$c instanceof Criteria && !$c instanceof Criterion )
            {
                throw new \InvalidArgumentException( "Arguments must be valid Criteria or Criterion objects" );
            }

            $this->criterias[] = $c;
        }

        return $this;
    }

    /**
     * Sets the maximum number of returned items to limit
     *
     * The offset is set with {@link offset()}
     *
     * @param integer $limit
     *
     * @return CriteriaCollection
     *
     * @throws \InvalidArgumentException if the provided $limit isn't an integer > 0
     */
    public function limit( $limit )
    {
        $limit = (int)$limit;

        if ( $limit <= 0 )
        {
            throw new \InvalidArgumentException( "Limit must be > 0" );
        }
        $this->limit = (int)$limit;

        return $this;
    }

    /**
     * Sets the offset from which items are to be returned to $offset
     *
     * Calling the method again will change the offset. The default offset value is 0.
     *
     * @param integer $offset
     *
     * @return CriteriaCollection
     */
    public function offset( $offset )
    {
        $this->offset = (int)$offset;
        return $this;
    }

    /**
     * Adds the given content type identifiers to the query
     *
     * Only elements of these class identifiers will be matched.
     * The method can be called multiple times to add more of them.
     *
     * @param string $contentTypeIdentifier$... One to many content type identifier strings
     *
     * @return CriteriaCollection
     *
     * @throws \InvalidArgumentException if one of the provided identifiers is not a string
     */
    public function type( $contentTypeIdentifier )
    {
        $contentTypeIdentifiers = func_get_args();

        foreach ( $contentTypeIdentifiers as $contentTypeIdentifier )
        {
            if ( !is_string( $contentTypeIdentifier ) )
            {
                throw new \InvalidArgumentException( "identifiers must be strings" );
            }
        }

        if ( $this->type === null )
        {
            $this->type = $contentTypeIdentifiers;
        }
        else
        {
            $this->type = array_unique( array_merge( $this->type, $contentTypeIdentifiers ) );
        }

        return $this;
    }

    /**
     * Adds one to many sort parameters.
     *
     * Several parameters can be provided at once, or the method can be called several times.
     *
     * @param SortByClause $sortBy$...
     *
     * @return CriteriaCollection
     */
    public function sortBy( SortByClause $sortBy )
    {
        foreach ( func_get_args() as $item )
        {
            if ( !$item instanceof SortByClause )
            {
                throw new \InvalidArgumentException( "Argument must be an instance of SortByClause" );
            }
            $this->sortItems[] = $item;
        }
        return $this;
    }
}
?>
