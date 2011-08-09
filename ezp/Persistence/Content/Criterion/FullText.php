<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\FullText
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\Operator\Specifications,
    ezp\Persistence\Content\CriterionInterface;

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
     * Creates a FullText criterion on $text
     *
     * @param string $target Not used
     * @param string $operator Not used
     * @param string $text The text to match on
     */
    public function __construct( $target, $operator, $value )
    {
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::LIKE, Specifications::FORMAT_SINGLE )
        );
    }
}
?>
