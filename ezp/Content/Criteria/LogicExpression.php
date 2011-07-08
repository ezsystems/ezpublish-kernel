<?php
/**
 * File containing LogicExpression class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

/**
 * Logic dispatcher for AND/OR association of criterias
 */
class LogicExpression
{
    /**
     * Returns a CriterionAnd object for AND logical association of criterias
     * Accepts any number of Criteria objects
     * Method name is lAnd() because "and" is a PHP reserved word
     * @return CriterionAnd
     * @throws \InvalidArgumentException If no criteria is passed as argument
     */
    public function lAnd()
    {
        $args = func_get_args();
        if ( empty( $args ) )
        {
            throw \InvalidArgumentException( __METHOD__ . " : At least one Criteria should be passed" );
        }

        return new CriterionAnd( $args );
    }

    /**
     * Returns a CriteriaOr object for OR logical association of criterias
     * Accepts any number of Criteria objects
     * Method name is lOr() because "or" is a PHP reserved word
     * @return CriterionOr
     * @throws \InvalidArgumentException If no criteria is passed as argument
     */
    public function lOr()
    {
        $args = func_get_args();
        if ( empty( $args ) )
        {
            throw \InvalidArgumentException( __METHOD__ . " : At least one Criteria should be passed" );
        }

        return new CriterionAnd( $args );
    }
}
?>
