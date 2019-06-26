<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;
use eZ\Publish\Core\Repository\Values\GeneratorProxyTrait;

/**
 * This class represents a (lazy loaded) proxy for a content type group value.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ContentTypeGroupProxy extends APIContentTypeGroup
{
    use GeneratorProxyTrait;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup|null */
    protected $object;

    public function getNames()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getNames();
    }

    public function getName($languageCode = null)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getName($languageCode);
    }

    public function getDescriptions()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getDescriptions();
    }

    public function getDescription($languageCode = null)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getDescription($languageCode);
    }
}
