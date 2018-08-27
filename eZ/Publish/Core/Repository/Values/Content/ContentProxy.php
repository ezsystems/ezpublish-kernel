<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
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

    /**
     * @var ContentContentInfoProxy|null
     */
    protected $contentInfoProxy;

    public function __get($name)
    {
        if ($name === 'id') {
            return $this->id;
        }

        if ($name === 'contentInfo') {
            return $this->getContentInfo();
        }

        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->$name;
    }

    public function __isset($name)
    {
        if ($name === 'id' || $name === 'contentInfo') {
            return true;
        }

        if ($this->object === null) {
            $this->loadObject();
        }

        return isset($this->object->$name);
    }

    /**
     * Return content info, in proxy form if we have not loaded object yet.
     *
     * For usage in among others DomainMapper->buildLocation() to make sure we can lazy load content info retrieval.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected function getContentInfo()
    {
        if ($this->object === null) {
            if ($this->contentInfoProxy === null) {
                $this->contentInfoProxy = new ContentContentInfoProxy($this, $this->id);
            }

            return $this->contentInfoProxy;
        } elseif ($this->contentInfoProxy !== null) {
            // Remove ref when we no longer need the proxy
            $this->contentInfoProxy = null;
        }

        return $this->object->getVersionInfo()->getContentInfo();
    }

    public function getVersionInfo()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getVersionInfo();
    }

    public function getContentType(): ContentType
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->getContentType();
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
