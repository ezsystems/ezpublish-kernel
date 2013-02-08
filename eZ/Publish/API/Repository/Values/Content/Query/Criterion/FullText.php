<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText class.
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
 * Full text search criterion
 *
 * The string provided in this criterion is matched as a full text query
 * against all indexed content objects in the storage layer.
 *
 * Normalization and querying capabilities might depend on the system
 * configuration or the used search engine and might differ. The following
 * basic query seamtics are supported:
 *
 * - If multiple words are specified an AND query is performed. OR queries are
 *   not yet supported.
 *
 * - Simple wild cards are supported. If an asterisk (*) is used at the end or
 *   beginning of a word this is translated into a wild card query. Thus "fo*"
 *   would match "foo" and "foobar", for example.
 *
 * - Simple stop word removal might be applied to the words provided in the
 *   query.
 */
class FullText extends Criterion implements CriterionInterface
{
    /**
     * Creates a FullText criterion on $text, using the IN Operator
     *
     * @param string $value The text to match on
     */
    public function __construct( $value )
    {
        parent::__construct( null, Operator::LIKE, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::LIKE, Specifications::FORMAT_SINGLE )
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
