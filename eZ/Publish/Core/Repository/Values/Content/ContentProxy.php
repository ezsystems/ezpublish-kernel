<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\Repository\Values\GeneratorProxyTrait;

/**
 * This class represents a (lazy loaded) proxy for a content value.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class ContentProxy extends APIContent
{
    use GeneratorProxyTrait;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|null
     */
    protected $object;

    public function getVersionInfo()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getVersionInfo();
    }

    public function getFieldValue($fieldDefIdentifier, $languageCode = null)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getFieldValue($fieldDefIdentifier, $languageCode);
    }

    public function getFields()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getFields();
    }

    public function getFieldsByLanguage($languageCode = null)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getFieldsByLanguage($languageCode);
    }

    public function getField($fieldDefIdentifier, $languageCode = null)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getField($fieldDefIdentifier, $languageCode);
    }
}
