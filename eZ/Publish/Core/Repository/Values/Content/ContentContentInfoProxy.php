<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;
use eZ\Publish\Core\Repository\Values\GeneratorProxyTrait;

/**
 * This class represents a (lazy loaded) proxy for a content info value, for use by ContentProxy.
 *
 * WARNING: Currently only meant for content where we know status, for instance in-direcly usage on search and on
 *          locations which currently only support published content. If any of those at some point start to support other
 *          statuses this should instead lazy load object in isDraft(), isPublished() and isTrashed() for safety.
 *
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ContentContentInfoProxy extends APIContentInfo
{
    use GeneratorProxyTrait;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo|null */
    protected $object;

    /** @var ContentProxy */
    protected $proxy;

    public function __construct(ContentProxy $proxy, int $id, $status = APIContentInfo::STATUS_PUBLISHED)
    {
        $this->proxy = $proxy;
        $this->id = $id;

        // See warning on class doc.
        parent::__construct(['status' => $status]);
    }

    /**
     * Get the inner content Info value object from ContentProxy.
     */
    protected function loadObject()
    {
        $this->object = $this->proxy->getVersionInfo()->getContentInfo();
        $this->proxy = null;
    }
}
