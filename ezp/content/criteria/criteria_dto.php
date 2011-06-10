<?php
/**
 * File containing CriteriaDTO class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

/**
 * Base Data Transfer Object (DTO) for criteria objects
 */
class CriteriaDTO implements CriteriaDTOInterface
{
    /**
     * Filter name for criteria
     * e.g. a field name or a supported filter name like "section", "depth", "priority"...
     * @var string
     */
    public $criteriaFilterName;

    /**
     * Value for filter (string representation)
     * @var string
     */
    public $criteriaFilterValue;

    /**
     * Filter operator ("=", "<", ">", ...)
     * @var string
     */
    public $criteriaFilterOperator;

    public function getName()
    {
        return $this->criteriaFilterName;
    }

    public function getValue()
    {
        return $this->criteriaFilterValue;
    }

    public function getOperator()
    {
        return $this->criteriaFilterOperator;
    }
}
?>
