<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\RemoteId class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criteria
 */
class RemoteId extends Criterion
{
    /**
     * Creates a new remoteId criterion
     * @param string|array(string) $remoteId One or more (as an array) remoteId
     */
    public function __construct( $remoteId )
    {
        $this->remoteIdList = $remoteId;
        $this->operator = Operator::IN;
    }

    /**
     * The list of remote ids to be matched against
     * @var array(string)
     */
    public $remoteIdList;
}
?>
