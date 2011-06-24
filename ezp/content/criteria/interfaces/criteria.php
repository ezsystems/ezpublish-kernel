<?php
/**
 * File containing CriteriaInterface interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\content\Criteria;

/**
 * Interface for every criterias
 */
interface CriteriaInterface
{
    /**
     * Must return a hash representation for current criteria
     * This hash representation must be an valid CriteriaDTO object
     * @see CriteriaDTOInterface
     * @return CriteriaDTOInterface
     */
    public function toHash();
}
?>
