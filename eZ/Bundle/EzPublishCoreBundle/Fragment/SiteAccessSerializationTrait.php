<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Fragment;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

trait SiteAccessSerializationTrait
{
    use SerializerTrait;

    public function serializeSiteAccess(SiteAccess $siteAccess, ControllerReference $uri)
    {
        // Serialize the siteaccess to get it back after. @see eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener
        $uri->attributes['serialized_siteaccess'] = json_encode($siteAccess);
        $uri->attributes['serialized_siteaccess_matcher'] = $this->getSerializer()->serialize(
            $siteAccess->matcher,
            'json'
        );
        if ($siteAccess->matcher instanceof SiteAccess\Matcher\CompoundInterface) {
            $subMatchers = $siteAccess->matcher->getSubMatchers();
            foreach ($subMatchers as $subMatcher) {
                $uri->attributes['serialized_siteaccess_sub_matchers'][get_class($subMatcher)] = $this->getSerializer()->serialize(
                    $subMatcher,
                    'json'
                );
            }
        }
    }
}
