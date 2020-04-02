<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * @internal
 */
final class ResourceProvider implements ResourceProviderInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function getFieldViewResources(): array
    {
        return $this->configResolver->getParameter('field_templates');
    }

    public function getFieldEditResources(): array
    {
        return $this->configResolver->getParameter('field_edit_templates');
    }

    public function getFieldDefinitionViewResources(): array
    {
        return $this->configResolver->getParameter('fielddefinition_settings_templates');
    }

    public function getFieldDefinitionEditResources(): array
    {
        return $this->configResolver->getParameter('fielddefinition_edit_templates');
    }
}
