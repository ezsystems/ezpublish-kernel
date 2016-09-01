<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output;

/**
 * Given a Value Object type as a string, and a set of parameters, generates a
 * link to a value object's REST representation.
 */
interface ValueHrefGeneratorInterface
{
    /**
     * Generates a link to a resource of $type, with $parameters.
     * @param string $type
     * @param array $parameters
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if a link can't be generated for $type
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if the parameters are invalid
     *
     * @return string
     */
    public function generate($type, array $parameters);
}
