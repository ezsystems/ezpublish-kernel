<?php
/**
 * File containing the PolicyCreateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
 */
class PolicyCreateStruct extends \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
{
    /**
     * List of limitations added to policy
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    protected $limitations = array();

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
                'module'    => $module,
                'function'  => $function
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
     *
     * @return void
     */
    public function addLimitation( Limitation $limitation )
    {
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }

}
