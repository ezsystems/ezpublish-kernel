<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Component\Serializer;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement;

final class URIElementNormalizer extends AbstractPropertyWhitelistNormalizer
{
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof URIElement;
    }

    /**
     * @see \eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement::__sleep
     */
    protected function getAllowedProperties()
    {
        return ['elementNumber', 'uriElements'];
    }
}
