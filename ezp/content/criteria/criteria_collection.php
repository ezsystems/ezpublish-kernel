<?php
/**
 * File containing ezp\content\Criteria\CriteriaCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\content\Criteria;
use ezp\content;

/**
 * Criteria collection to be used to "find" a content in a subtree
 * Example :
 * <code>
 * use ezp\content\Repository as ContentRepository;
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
 * @property FieldCriteria $field The field criteria
 * @property MetadataCriteria $meta Metadata criteria (published date, section, path, owner...)
 * @property LocationCriteria $location Location criteria for subtree filtering (most likely starting point)
 */
class CriteriaCollection
{
    /**
     * Parent location to retrieve content from
     * @var ezp\content\Location
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
     * Criterias for content sorting
     * @var SortCriteriaCollection
     */
    protected $sortCriterias = array();

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
     * Generic getter
     * Returns a criteria from its name, if available
     * @param string $criteriaName
     * @return Criteria
     */
    public function __get( $criteriaName )
    {
        $criteria = null;
        switch ( $criteriaName )
        {
            case "field":
                $criteria = new FieldCriteria();
                break;

            case "meta":
                $criteria = new MetadataCriteria();
                break;

            case "location":
                $criteria = new LocationCriteria();
                break;

            default:
                throw \InvalidArgumentException( "Criteria '{$criteriaName}' is not supported" );
        }

        return $criteria;
    }

    /**
     * Adds filter criterias
     * Arguments must be valid Criteria or Criterion objects
     * @return CriteriaCollection
     * @throws \InvalidArgumentException If at least one of the arguments is not a valid Criteria/Criterion object
     */
    public function where()
    {
        $args = func_get_args();

        foreach ( $args as $c )
        {
            if ( !$c instanceof Criteria && !$c instanceof Criterion )
            {
                throw new \InvalidArgumentException( "Arguments must be valid Criteria or Criterion objects" );
            }

            $this->criterias[] = $c;
        }

        return $this;
    }

    public function limit( $limit )
    {
        $this->limit = (int)$limit;
        return $this;
    }

    public function offset( $offset = 0 )
    {
        $this->offset = (int)$offset;
        return $this;
    }

    public function type( $contentTypeIdentifier )
    {
        $this->type = (string)$contentTypeIdentifier;
        return $this;
    }
}
?>
