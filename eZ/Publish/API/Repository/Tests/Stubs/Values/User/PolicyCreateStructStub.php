<?php
/**
 * File containing the PolicyCreateStructStub class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
 */
class PolicyCreateStructStub extends PolicyCreateStruct
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    private $limitations;

    /**
     * Instantiates a policy create struct.
     *
     * @param string $module
     * @param string $function
     */
    public function __construct( $module, $function )
    {
        parent::__construct(
            array(
                'module' => $module,
                'function' => $function
            )
        );
    }

    /**
     * Returns list of limitations added to policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }

    /**
     * Adds a limitation with the given identifier and list of values
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @return void
     */
    public function addLimitation( Limitation $limitation )
    {
        $this->limitations[] = $limitation;
    }

}
