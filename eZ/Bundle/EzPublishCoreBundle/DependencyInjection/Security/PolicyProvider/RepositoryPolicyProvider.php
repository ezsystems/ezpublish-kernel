<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider;

/**
 * @deprecated Deprecated since 7.1. No longer used. System policies configuration was moved to the eZ/Publish/Core/settings/policies.yml.
 */
class RepositoryPolicyProvider extends YamlPolicyProvider
{
    public function getFiles()
    {
        return [];
    }
}
