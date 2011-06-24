<?php
/**
 * File containing LocationCriteria class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\content\Criteria;
use ezp\content;

class LocationCriteria extends Criteria implements CriteriaInterface
{
    protected $parentLocation;

    protected $depth;

    public function __construct()
    {

    }

    /**
     * Adds a subtree limitation under $parentLocation.
     * Limits to direct children only
     * @param Content\Location $parentLocation
     */
    public function isChildOf( Content\Location $parentLocation )
    {
        $this->parentLocation = $parentLocation;
        $this->depth = 1;
    }

    /**
     * Adds a subtree limitation under $parentLocation
     * Depth in subtree can be limited by $depth
     * @param Content\Location $parentLocation
     * @param int $depth Default is -1 (no depth limitation). Setting this to 1 limits to direct children
     */
    public function subTree( Content\Location $parentLocation, $depth = -1 )
    {
        $this->parentLocation = $parentLocation;
        $this->depth = $depth;
    }

    /**
     * @see CriteriaInterface::toHash()
     */
    public function toHash()
    {
        $dto = new CriteriaDTO();
        $dto->criteriaFilterName = "location";
        $dto->criteriaFilterValue = $this->parentLocation;
        $dto->criteriaFilterOperator = $this->depth;

        return $dto;
    }
}
?>
