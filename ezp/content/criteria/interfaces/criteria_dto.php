<?php
/**
 * File containing CriteriaDTOInterface class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

interface CriteriaDTOInterface
{
    /**
     * Returns filter name for corresponding criteria
     * @return string
     */
    public function getName();

    /**
     * Returns a string representation of filter value
     * Making the string representation could be delegated
     * @return string
     */
    public function getValue();

    /**
     * Returns the logic operator ("=", "<", ">", ...)
     * @return string
     */
    public function getOperator();
}
?>
