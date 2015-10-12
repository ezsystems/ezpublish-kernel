<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\QueryType;

use eZ\Publish\Core\QueryType\QueryType;

/**
 * Used by the named_queries scenario.
 */
class PagerDisabledQueryType extends EmptyQuery implements QueryType
{
    public static function getName()
    {
        return 'EzPlatformBehatBundle:PagerDisabled';
    }
}
