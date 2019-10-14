<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\Core\Repository\Values\GhostObjectProxyTrait;

final class ContentTypeProxy extends ContentType
{
    use GhostObjectProxyTrait;

    public function getContentTypeGroups()
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getContentTypeGroups();
    }

    public function getFieldDefinitions()
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getFieldDefinitions();
    }

    public function getFieldDefinition($fieldDefinitionIdentifier)
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getFieldDefinition($fieldDefinitionIdentifier);
    }

    public function getDescriptions()
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getDescriptions();
    }

    public function getDescription($languageCode = null)
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getDescription($languageCode);
    }

    public function getNames()
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getNames();
    }

    public function getName($languageCode = null)
    {
        if ($this->isInitialized()) {
            $this->initialize();
        }

        return parent::getName($languageCode);
    }
}
