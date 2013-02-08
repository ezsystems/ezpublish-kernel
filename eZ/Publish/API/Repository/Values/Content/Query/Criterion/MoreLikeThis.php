<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\MoreLikeThis class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A more like this criterion is matched by content which contains similar terms
 * found in the given content, text or url fetch
 */
class MoreLikeThis extends Criterion implements CriterionInterface
{
    const CONTENT = 1;
    const TEXT = 2;
    const URL = 3;

    /**
     * The type of the parameter from which terms are extracted for finding similar objects
     *
     * @var int
     */
    protected $type;

    /**
     * Creates a new more like this criterion
     *
     * @param int $type the type (one of CONTENT,TEXT,URL)
     * @param mixed $value the value depending on the type
     *
     * @throws \InvalidArgumentException if the value type doesn't match the expected type
     */
    public function __construct($type,  $value )
    {
        $this->type = $type;

        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::EQ, Specifications::FORMAT_SINGLE )
        );

    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
