<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * A criterion that matches content based on its language code and always-available state
 *
 * Supported operators:
 * - IN: matches against a list of language codes
 * - EQ: matches against one language code
 */
class LanguageCode extends Criterion implements CriterionInterface
{
    /**
     * Switch for matching Content that is always-available
     *
     * @var boolean
     */
    public $matchAlwaysAvailable;

    /**
     * Creates a new LanguageCode criterion
     *
     * @param string|string[] $value One or more language codes that must be matched
     * @param boolean $matchAlwaysAvailable Denotes if always-available Content is to be matched regardless
     *                                      of language codes
     *
     * @throws \InvalidArgumentException if non string value is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value, $matchAlwaysAvailable = false )
    {
        if ( !is_bool( $matchAlwaysAvailable ) )
        {
            throw new InvalidArgumentType( "matchAlwaysAvailable", "boolean", $matchAlwaysAvailable );
        }

        $this->matchAlwaysAvailable = $matchAlwaysAvailable;
        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
        );
    }

    /**
     * @todo needs to be updated for $matchAlwaysAvailable
     */
    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
