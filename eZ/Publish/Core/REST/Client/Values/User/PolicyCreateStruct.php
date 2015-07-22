<?php

/**
 * File containing the PolicyCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
 */
class PolicyCreateStruct extends APIPolicyCreateStruct
{
    /**
     * List of limitations added to policy.
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
    public function __construct($module, $function)
    {
        parent::__construct(
            array(
                'module' => $module,
                'function' => $function,
            )
        );
    }

    /**
     * Returns list of limitations added to policy.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }

    /**
     * Adds a limitation with the given identifier and list of values.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function addLimitation(Limitation $limitation)
    {
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }
}
