<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\PlatformBehatBundle\Controller;

use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

class ExceptionController
{
    public function throwRepositoryUnauthorizedAction($module = 'foo', $function = 'bar', $properties = [])
    {
        throw new UnauthorizedException($module, $function, $properties);
    }
}
