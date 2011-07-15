<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Permission class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface,
    InvalidArgumentException;

/**
 */
class Permission extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Permission criterion
     *
     * Only content $userId has $permission for will be matched
     *
     * @param integer $userId
     * @param mixed $permission
     *
     * @throws InvalidArgumentException if $userId isn't numeric
     */
    public function __construct( $metadata, $operator, $value )
    {
        if ( !is_numeric( $userId ) )
        {
            throw new InvalidArgumentException( '$userId must be numeric' );
        }
        $this->userId = $userId;
        $this->operation = $operation;
    }

    /**
     * The id of the user permissions are matched against
     * @var integer
     */
    public $userId;

    /**
     * The operation to match against
     * @var mixed
     * @todo Elaborate how an operation is given
     */
    public $operation;
}
?>
